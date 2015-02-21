<?php	

namespace LaPoiz\WindBundle\Command;

use LaPoiz\WindBundle\core\note\NoteMaree;
use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class CreateNoteCommand extends ContainerAwareCommand  {

    const HEURE_MATIN = 9;
    const HEURE_SOIR = 19;

 	protected function configure()
    {
        $this
            ->setName('lapoiz:createNote')
            ->setDescription('Create note for each spot, with data on DB')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        // récupere tous les spots
        $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAll();

    	
    	foreach ($listSpot as $spot) {
    		$output->write('<info>Spot '.$spot->getNom().' - </info>');

            $tabNotes = array();
            // Pour les 7 prochains jours
            $day= new \DateTime("now");
            for ($nbPrevision=0; $nbPrevision<7; $nbPrevision++) {
                $tabNotes[$day->format('Y-m-d')]=array();
                $day->modify('+1 day');
            }

            //********** Marée **********
            // récupére la marée du jour
            // Note la marée en fonction des restrictions et entre 9h et 19h
            $listeMareeFuture = $em->getRepository('LaPoizWindBundle:MareeDate')->getFuturMaree($spot);
            foreach ($listeMareeFuture as $mareeDate) {
                NoteMaree::calculNoteMaree($spot, $tabNotes, $mareeDate);
            }



            //********** Orientation **********
            if ($spot->getWindOrientation() !=null && count($spot->getWindOrientation())>0) {

                // récupére l'orientation du vent sur WindFinder
                foreach ($spot->getDataWindPrev() as $dataWindPrev) {
                    // Recherche WindFinder
                    if ($dataWindPrev->getWebSite()->getNom()==WebsiteGetData::windFinderName) {
                        // $dataWindPrev -> WindFinder + spot

                        // Création d'un tableau des orientations
                        $tabOrientation = array();
                        foreach ($spot->getWindOrientation() as $windOrientation) {
                            $tabOrientation[$windOrientation->getOrientation()]=$windOrientation->getState();
                        }

                        $previsionDateListe = $this->getDoctrine()->getRepository('LaPoizWindBundle:PrevisionDate')->findLastCreated($dataWindPrev);

                        // Pour chaque jour
                        foreach ($previsionDateListe as $previsionDate) {

                            // vérifie que $previsionDate->getDatePrev() soit dans $tabNotes

                            // tableau du nb d'orientation par état
                            $tabNbOrientation = array("OK"=>0, "warn"=>0, "KO"=>0);

                            // regarde quel est l'etat de l'orientation durant la période de navigation
                            foreach ($previsionDate->getListPrevision() as $prevision) {
                                // if (dans le creneau horaire) {
                                // $orientationState = $tabOrientation[getOrientation($prevision->getOrientation())]]
                                // $tabNbOrientation[$orientationState] = $tabNbOrientation[$orientationState]+1;
                                // }
                            }
                            // calcule la note pour la journnée
                            // $tabNotes[$previsionDate->getDatePrev()->format("Y-m-d")]["windOrientation"]=$note;
                            // $tabNotes[$previsionDate->getDatePrev()->format("Y-m-d")]["windOrientationData"]=$nbHeureOK;
                        }

                    }
                }
            }

            //********** Température **********
            ///////// récupére la temperature dans le spot - Dans MeteoFranceGetData (par ex)

            // calcul la température moyenne entre 9h et 19h
            // Note la température entre 9h et 19h en fonction:
            //  du temps ou la temperature est inf à ...
            //  du temps ou la temperature est entre ... et ...
            //  du temps ou la temperature est supérieur à ...

            //********** Température de l'eau **********
            // rien n'existe actuellement
            // récupére la temperature de l'eau dans la journée (elle ne varie quasi pas
            // calcul de la note en fonction de la T°C


            //********** Wind **********
            // récupére toutes les prévisions de tous les sites
            foreach ($spot->getDataWindPrev() as $dataWindPrev) {
                $previsionDateListe = $this->getDoctrine()->getRepository('LaPoizWindBundle:PrevisionDate')->findLastCreated($dataWindPrev);

                // Spécial pour MeteoFrance ?

                // Pour chaque jour
                foreach ($previsionDateListe as $previsionDate) {
                    $tabInf12Nds = 0;
                    $tabSupr12Nds = 0;
                    $tabSupr15Nds = 0;

                    foreach ($previsionDate->getListPrevision() as $prevision) {

                        // vérifie que $previsionDate->getDatePrev() soit dans $tabNotes

                        // si dans la tranche horaire de $prevision->getTime()
                        $wind = $prevision->getWind();
                        if ($wind < 12 ) {
                            $tabInf12Nds++;
                        } else { //($wind >= 12 )
                            $tabSupr12Nds++;
                            if ($wind > 15 ) {
                                $tabSupr15Nds++;
                            }
                        }
                        // calcul la note pour la journée
                        // $tabNotes[$previsionDate->getDatePrev()->format("Y-m-d")]["wind"]=$note;
                        // $tabNotes[$previsionDate->getDatePrev()->format("Y-m-d")]["wind"]=$nbHeureOK;
                    }
                }

            }

            //********** Précipitation **********
            // Depuis WindFinder

            // Boucle sur tous les sites
            foreach ($spot->getDataWindPrev() as $dataWindPrev) {
                // Recherche WindFinder
                if ($dataWindPrev->getWebSite()->getNom()==WebsiteGetData::windFinderName) {
                    // $dataWindPrev -> WindFinder + spot

                    // Récupére les dernieres prévisions
                    $previsionDateListe = $this->getDoctrine()->getRepository('LaPoizWindBundle:PrevisionDate')->findLastCreated($dataWindPrev);

                    // Pour chaque jour
                    foreach ($previsionDateListe as $previsionDate) {

                        // vérifie que $previsionDate->getDatePrev() soit dans $tabNotes

                        // nb de fois sous l'eau + quantité
                        $tempsFortePluie = 0;
                        $tempsPetitePluie = 0;
                        $tempsSansPluie = 0;

                        // regarde la précipitation durant la période de navigation
                        foreach ($previsionDate->getListPrevision() as $prevision) {
                            // if (dans le creneau horaire) {
                            $precipitation = $prevision->getPrecipitation();
                            //if ($precipitation=0) {// Attention c'est un String dans la BD
                            //$tempsSansPluie++;
                            //} elseif ($precipitation<??) {
                                //$tempsPetitePluie++;
                            //} elseif ($precipitation>=??) {
                            //$tempsFortePluie++;
                            //}

                            // }
                        }
                        // calcule la note pour la journnée
                        // $tabNotes[$previsionDate->getDatePrev()->format("Y-m-d")]["Precipitation"]=$note;
                        // $tabNotes[$previsionDate->getDatePrev()->format("Y-m-d")]["PrecipitationData"]=$nbHeureOK;
                    }

                }
            }


            //********** Météo **********
            // récupére la météo: Meteo France + couverture nuageuse
            // calcul le nombre d'heure au soleil
            // calcul le nombre d'heure sous les nuages
            // calcul le nombre d'heure sous les orages
            // Calcul la note

            /////////////// A EFFACER ///////////////////////////////



    			
    		// save data
    		$websiteGetData=WebsiteGetData::getWebSiteObject($dataWindPrev);// return WindguruGetData or MeteoFranceGetData... depend of $dataWindPrev
    		
    		$data=$websiteGetData->getDataFromURL($dataWindPrev); // array($result,$chrono)
    		$output->write('<info>    get data: '.$data[1].'</info>');
    		$analyse=$websiteGetData->analyseDataFromPage($data[0],$dataWindPrev->getUrl()); // array($result,$chrono)
    		$output->write('<info>    analyse: '.$analyse[1].'</info>');
    		$transformData=$websiteGetData->transformDataFromTab($analyse[0]); // array($result,$chrono)
    		$output->write('<info>    transforme data: '.$transformData[1].'</info>');
    		$saveData=$websiteGetData->saveFromTransformData($transformData[0],$dataWindPrev,$em); // array(array $prevDate,$chrono)
    		$output->writeln('<info>    save data: '.$saveData[1].'</info>');
    		$output->writeln('<info>******************************</info>');
    	}


        // Get Marée
        $spotList = $em->getRepository('LaPoizWindBundle:Spot')->findAll();

        $output->writeln('<info>************** GET MAREE ****************</info>');
        foreach ($spotList as $spot) {
            if ($spot->getMareeURL()!=null) {
                $prevMaree = MareeGetData::getMaree($spot->getMareeURL());
                MareeGetData::saveMaree($spot, $prevMaree, $em, $output);
            }
        }
    }

}