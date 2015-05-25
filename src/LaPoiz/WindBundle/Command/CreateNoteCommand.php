<?php	

namespace LaPoiz\WindBundle\Command;

use LaPoiz\WindBundle\core\note\NoteMaree;
use LaPoiz\WindBundle\core\note\NoteWind;
use LaPoiz\WindBundle\core\note\NoteMeteo;
use LaPoiz\WindBundle\core\note\NoteTemp;
use LaPoiz\WindBundle\core\note\ManageNote;
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
    		$output->writeln('<info>Note du Spot '.$spot->getNom().' - </info>');

            // On efface les vielles notes (avant aujourd'hui)
            ManageNote::deleteOldData($spot, $em);

            $tabNotes = array();
            $tabListePrevisionDate = array(); // tableau des liste des PrevisionDate, chaque cellule correspondant à une dateprev

            // Pour les 7 prochains jours
            $day= new \DateTime("now");
            for ($nbPrevision=0; $nbPrevision<7; $nbPrevision++) {
                $tabNotes[$day->format('Y-m-d')]=-1;
                $tabListePrevisionDate[$day->format('Y-m-d')]=array();
                $day->modify('+1 day');
            }

            $output->writeln('<info>  Note la maree </info>');

            //********** Marée **********
            // récupére la marée du jour
            // Note la marée en fonction des restrictions
            $listeMareeFuture = $em->getRepository('LaPoizWindBundle:MareeDate')->getFuturMaree($spot);
            foreach ($listeMareeFuture as $mareeDate) {
                $noteDate = ManageNote::getNotesDate($spot,$mareeDate->getDatePrev(), $em);
                $tabNotes = NoteMaree::calculNoteMaree($spot, $tabNotes, $mareeDate);
                $keyDate=$mareeDate->getDatePrev()->format('Y-m-d');
                $noteMaree=$tabNotes[$keyDate]["marée"];
                $noteDate->setNoteMaree($noteMaree);
                $output->writeln('<info>'.$keyDate.': '.$noteMaree.'</info> ');
                $em->persist($noteDate);
            }


            $output->writeln('<info>  Note la Météo</info>');
            //********** Meteo **********

            //list des PrevisionDate pour les prochain jour, pour le spot pour tous les websites
            $listALlPrevisionDate = $em->getRepository('LaPoizWindBundle:PrevisionDate')->getPrevDateAllWebSiteNextDays($spot);

            foreach ($listALlPrevisionDate as $previsionDate) {
                // ajouter au tableau de la cellule du jour de $tabListePrevisionDate
                $tabListePrevisionDate[$previsionDate->getDatePrev()->format('Y-m-d')][]=$previsionDate;
            }

            // Save Note on spot
            foreach ($tabNotes as $keyDate=>$note) {
                if ($tabListePrevisionDate[$keyDate] != null && count($tabListePrevisionDate[$keyDate])>0) {
                    $noteDate = ManageNote::getNotesDate($spot,\DateTime::createFromFormat('Y-m-d',$keyDate), $em);
                    $output->write('<info>'.$keyDate.': ');
                    $noteDate->setNoteWind(NoteWind::calculNoteWind($tabListePrevisionDate[$keyDate]));
                    $output->write(' wind:'.$noteDate->getNoteWind().' - ');
                    $noteDate->setNoteMeteo(NoteMeteo::calculNoteMeteo($tabListePrevisionDate[$keyDate]));
                    $output->write(' meteo:'.$noteDate->getNoteMeteo().' - ');
                    $noteDate->setNoteTemp(NoteTemp::calculNoteTemp($tabListePrevisionDate[$keyDate]));
                    $output->write(' temp:'.$noteDate->getNoteTemp());
                    $output->writeln('</info>');
                    $em->persist($noteDate);
                }
            }

            $em->flush();

            //********** Température de l'eau **********
            // rien n'existe actuellement
            // récupére la temperature de l'eau dans la journée (elle ne varie quasi pas
            // calcul de la note en fonction de la T°C

    		$output->writeln('<info>******************************</info>');
    	}

    }

}