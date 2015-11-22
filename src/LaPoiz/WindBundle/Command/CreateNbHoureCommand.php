<?php	

namespace LaPoiz\WindBundle\Command;

use LaPoiz\WindBundle\core\nbHoure\NbHoureMeteo;
use LaPoiz\WindBundle\core\nbHoure\NbHoureNav;
use LaPoiz\WindBundle\core\note\NbHoureWind;
use LaPoiz\WindBundle\core\note\ManageNote;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class CreateNbHoureCommand extends ContainerAwareCommand  {

    const HEURE_MATIN = 8;
    const HEURE_SOIR = 20;

 	protected function configure()
    {
        $this
            ->setName('lapoiz:createNbHoure')
            ->setDescription('Calculate numbre of navigation houre for each spot, with data on DB')
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

            list($tabDataNbHoureNav,$tabDataMeteo)=NbHoureNav::createTabNbHoureNav($spot, $em);
            $tabNbHoureNav=NbHoureNav::calculateNbHourNav($tabDataNbHoureNav);


            // Save nbHoure on spot
            foreach ($tabNbHoureNav as $keyDate=>$tabWebSite) {
                $output->write('<info>' . $keyDate . ': ');
                foreach ($tabWebSite as $keyWebSite=>$nbHoureNav) {
                    $output->writeln('<info>    '.$keyWebSite.' : '.$nbHoureNav.'</info> ');
                    $noteDates=ManageNote::getNotesDate($spot, \DateTime::createFromFormat('Y-m-d',$keyDate), $em);
                    $nbHoureNavObj=ManageNote::getNbHoureNav($noteDates, $keyWebSite, $em);
                    $nbHoureNavObj->setNbHoure($nbHoureNav);
                    $em->persist($nbHoureNavObj);
                    $em->persist($noteDates);
                }
            }

            // Save meteo
            $tabMeteo=NbHoureMeteo::calculateMeteoNav($tabDataMeteo);
            foreach ($tabMeteo as $keyDate=>$tabMeteoDay) {
                $output->writeln('<info>   Calcule Meteo of '.$keyDate.'</info> ');
                $noteDates=ManageNote::getNotesDate($spot, \DateTime::createFromFormat('Y-m-d',$keyDate), $em);
                $noteDates->setTempMax($tabMeteoDay["tempMax"]);
                $noteDates->setTempMin($tabMeteoDay["tempMin"]);
                $noteDates->setMeteoBest($tabMeteoDay["meteoBest"]);
                $noteDates->setMeteoWorst($tabMeteoDay["meteoWorst"]);

                $em->persist($noteDates);
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