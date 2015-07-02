<?php	

namespace LaPoiz\WindBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

//use LaPoiz\WindBundle\Entity\DataWindPrev;
use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;


class GetDataWindguruCommand extends ContainerAwareCommand  {

 	protected function configure()
    {
        $this
            ->setName('lapoiz:getDataWindguru')
            ->setDescription('Get Data from web site: Meteo France')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
		// get list of URL 
    	$em = $this->getContainer()->get('doctrine.orm.entity_manager');
    	$dataWindPrevList = $em->getRepository('LaPoizWindBundle:DataWindPrev')->findAll();
    	
    	foreach ($dataWindPrevList as $dataWindPrev) {
            if ($dataWindPrev->getWebSite()->getNom() == WebsiteGetData::windguruName) {
                GetDataCommand::getDataFromDataWindPrev($dataWindPrev, $output, $em);
            }
            if ($dataWindPrev->getWebSite()->getNom() == WebsiteGetData::windguruProName) {
                GetDataCommand::getDataFromDataWindPrev($dataWindPrev, $output, $em);
            }
    	}
    }
}