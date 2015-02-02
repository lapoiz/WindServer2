<?php	

namespace LaPoiz\WindBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

//use LaPoiz\WindBundle\Entity\DataWindPrev;
use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;
use LaPoiz\WindBundle\core\maree\MareeGetData;


class GetDataCommand extends ContainerAwareCommand  {

 	protected function configure()
    {
        $this
            ->setName('lapoiz:getData')
            ->setDescription('Get Data from web site')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
		// get list of URL 
    	$em = $this->getContainer()->get('doctrine.orm.entity_manager');
    	$dataWindPrevList = $em->getRepository('LaPoizWindBundle:DataWindPrev')->findAll();
    	
    	foreach ($dataWindPrevList as $dataWindPrev) {
    		$output->write('<info>Spot '.$dataWindPrev->getSpot()->getNom().' - </info>');
    		 
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