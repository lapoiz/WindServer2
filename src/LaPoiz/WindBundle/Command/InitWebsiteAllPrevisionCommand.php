<?php	

namespace LaPoiz\WindBundle\Command;

use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;
use LaPoiz\WindBundle\Entity\DataWindPrev;
use LaPoiz\WindBundle\Entity\WebSite;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class InitWebsiteAllPrevisionCommand extends ContainerAwareCommand  {

 	protected function configure()
    {
        $this
            ->setName('lapoiz:initWebsiteAllPrev')
            ->setDescription('Création pour tout les spots d un faux website qui englobe toutes les previsions des autres sites')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        // récupere tous les spots
        $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAll();

        //$allPrevWebSite = $em->getRepository('LaPoizWindBundle:WebSite')->findWithName(WebsiteGetData::allPrevName);
        $allPrevWebSite = new WebSite();
        $allPrevWebSite->setNom(WebsiteGetData::allPrevName);
        $allPrevWebSite->setUrl("www.lapoiz.com");
        $allPrevWebSite->setLogo("Lapoiz.png");
        $em->persist($allPrevWebSite);

    	foreach ($listSpot as $spot) {
    		$output->writeln('<info>Note du Spot '.$spot->getNom().' - </info>');

            $dataWindPrevAllPrev = new DataWindPrev();
            $dataWindPrevAllPrev->setUrl('http://www.lapoiz.com');
            $dataWindPrevAllPrev->setWebsite($allPrevWebSite);
            $dataWindPrevAllPrev->setCreated(new \DateTime("now"));

            $dataWindPrevAllPrev->setSpot($spot);
            $spot->addDataWindPrev($dataWindPrevAllPrev);

            $em->persist($dataWindPrevAllPrev);
            $em->persist($spot);
    	}
        $output->writeln('<info>**** END ****</info>');
        $em->flush();
    }

}