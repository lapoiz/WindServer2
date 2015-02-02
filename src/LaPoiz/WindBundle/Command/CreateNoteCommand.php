<?php	

namespace LaPoiz\WindBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class CreateNoteCommand extends ContainerAwareCommand  {

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

            // Pour les 7 prochains jours

            // getNotesDate(day) du jour (si inexistant -> créé)

            //********** Marée **********
            // récupére la marée du jour
            // Note la marée en fonction des restrictions et entre 9h et 19h

            //********** Orientation **********
            // récupére l'orientation du vent du jour
            // calcul le temps (en heure) ou c'est OK entre 9h et 19h
            // Note l'orientation en fonction des données de Windguru entre 9h et 19h

            //********** Température **********
            // récupére la temperature dans la journée
            // calcul la température moyenne entre 9h et 19h
            // Note la température entre 9h et 19h en fonction:
            //  du temps ou la temperature est inf à ...
            //  du temps ou la temperature est entre ... et ...
            //  du temps ou la temperature est supérieur à ...

            //********** Température de l'eau **********
            // récupére la temperature de l'eau dans la journée (elle ne varie quasi pas
            // calcul de la note en fonction de la T°C

            //********** Wind **********
            // récupére toutes les prévisions pour le jour
            // Pour météoFrance : note direct
            // Pour Windguru et WindFinder : moyenne dans la journée
            // Wind max et min
            // surface au dessus de 12Nd
            // surface au dessus de 15Nd

            //********** Précipitation **********
            // récupére les précipitations de la journée
            // calcul le nombre d'heure sous l'eau
            // calcul la note

            //********** Météo **********
            // récupére la météo: Meteo France + couverture nuageuse
            // calcul le nombre d'heure au soleil
            // calcul le nombre d'heure sous les nuages
            // calcul le nombre d'heure sous les orages
            // Calcul la note

            /////////////// A EFFACER ///////////////////////////////

            // get each web site
    		$output->writeln('<info>site '.$dataWindPrev->getWebSite()->getNom().' -> '.$dataWindPrev->getUrl().'</info>');
    			
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