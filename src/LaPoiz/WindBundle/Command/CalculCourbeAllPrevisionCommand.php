<?php	

namespace LaPoiz\WindBundle\Command;

use LaPoiz\WindBundle\core\tempWater\TempWaterGetData;
use LaPoiz\WindBundle\core\websiteDataManage\AllPrevGetData;
use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class CalculCourbeAllPrevisionCommand extends ContainerAwareCommand  {

    const HEURE_MATIN = 8;
    const HEURE_SOIR = 20;

 	protected function configure()
    {
        $this
            ->setName('lapoiz:calculAllPrevision')
            ->setDescription('Create the prevision with all prevision for each spot.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        // récupere tous les spots
        $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAll();
        $allPrevWebSite = $em->getRepository('LaPoizWindBundle:WebSite')->findWithName(WebsiteGetData::allPrevName);

    	foreach ($listSpot as $spot) {
            $output->writeln('<info>*** Spot ' . $spot->getNom() . ' ***</info>');

            // Find dataWindPrev with allPrevWebsite and spot
            $dataWindPrevAllPrev = $em->getRepository('LaPoizWindBundle:DataWindPrev')->getWithWebsiteAndSpot($allPrevWebSite, $spot);

            // Delete all old data
            AllPrevGetData::deleteOldData($dataWindPrevAllPrev, $em);


            // Construit un tableau avec toutes les heures pour les 7 prochains jours

            // Pour chaque site de prevision remplis le tableau avec ses prévisions (vent, vents max, orientation)

            // Pour chaque site remplis les cases vide en fonction des éléments remplis

            // Calcul la moyenne pour chaque heure (avec coef pour le spot)

        }

        $em->flush();
    }
}