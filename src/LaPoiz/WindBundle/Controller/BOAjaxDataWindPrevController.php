<?php
namespace LaPoiz\WindBundle\Controller;

use Symfony\Component\HttpKernel\Debug\ExceptionHandler;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use LaPoiz\WindBundle\Entity\DataWindPrev;

use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;
use LaPoiz\WindBundle\core\websiteDataManage\WindguruGetData;
use LaPoiz\WindBundle\core\websiteDataManage\MeteoFranceGetData;
use LaPoiz\WindBundle\core\websiteDataManage\MeteorologicGetData;
use LaPoiz\WindBundle\core\WindData;



class BOAjaxDataWindPrevController extends Controller
{
	/**
	 * @Template()
	 */
	// for testing: http://localhost/WindServer/web/app_dev.php/lapoiz/ajax/dataWindPrev/test/1
	public function testAction($spotId=null,$id=null,$step=1)
	{
		try {
			$message='';
			$em = $this->container->get('doctrine.orm.entity_manager');
	
			if (isset($id)) {
				$dataWindPrev = $em->find('LaPoizWindBundle:DataWindPrev', $id);
				$websiteGetData=WebSiteGetData::getWebSiteObject($dataWindPrev);
				
				if (is_null($websiteGetData)) {
					$message=$this->get('translator')->trans('error.message.ajax.website.not.found',
						array('%websiteName%' => $dataWindPrev->getWebsite()->getNom()));
					return $this->errorReturn($message,$dataWindPrev);
					
				}
				
				$data=$websiteGetData->getAndCleanData($dataWindPrev); // array($step,$result,$chrono)
				
				return $this->container->get('templating')->renderResponse(
					'LaPoizWindBundle:BackOffice/Spot/Ajax:testdataWindPrev.html.twig',
					array(	'chrono' => $data[2],
							'step' => $data[0],
							'info' => $data[1],
        					'typeDisplay' => $websiteGetData->typeDisplay($data[0]),
							'dataWindPrev' => $dataWindPrev ));
			} else {
				$result="No find DataWindPrev.id=".$id;
			}
		} 	catch (Exception $e) {
        	$result=toString($e);
    	} 	catch (\ErrorException $ee) {
    		$result=$ee->getMessage();
    	}
    	
        return $this->container->get('templating')->renderResponse(
        		'LaPoizWindBundle:BackOffice/Spot/Ajax:testdataWindPrev.html.twig',
        		array(	'chrono' => 0,
        				'step' => 10,
        				'info' => $result,
        				'typeDisplay' => WebsiteGetData::typeDisplay(-1),
        				'dataWindPrev' => $dataWindPrev ));
	}


	
	/**
	* @Template()
	*/
	// http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/dataWindPrev/test/step1/3
	public function testStep1Action($id=null)
	{
		try {
			$message='';
			$em = $this->container->get('doctrine.orm.entity_manager');
	
			if (isset($id)) {
				$dataWindPrev = $em->find('LaPoizWindBundle:DataWindPrev', $id);
				$websiteGetData=WebSiteGetData::getWebSiteObject($dataWindPrev);
				
				$data=$websiteGetData->getDataFromURL($dataWindPrev); // array($result,$chrono)
				
				return $this->container->get('templating')->renderResponse(
					'LaPoizWindBundle:BackOffice/Spot/Ajax:testdataWindPrev.html.twig',
					array(	'chrono' => $data[1],
							'info' => $websiteGetData->displayGetData($data[0]),
        					'typeDisplay' => $websiteGetData->typeDisplay(1),
        					'step' => 1,
        					'dataWindPrev' => $dataWindPrev));
			} else {
				$result="No find DataWindPrev.id=".$id;
			}
		} 	catch (Exception $e) {
        	$result=toString($e);
    	} 	catch (\ErrorException $ee) {
    		$result=$ee->getMessage();
    	}
        return $this->container->get('templating')->renderResponse(
        		'LaPoizWindBundle:BackOffice/Spot/Ajax:testdataWindPrev.html.twig',
        		array(	'chrono' => 0,
        				'step' => 10,
        				'info' => $result,
        				'typeDisplay' => WebsiteGetData::typeDisplay(-1),
        				'dataWindPrev' => $dataWindPrev ));
	}
	
	
	/**
	 * @Template()
	 */
	// http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/dataWindPrev/test/step2/1
	public function testStep2Action($id=null)
	{
		try {
			$message='';
			$em = $this->container->get('doctrine.orm.entity_manager');
			$step=2;

			if (isset($id)) {
				$dataWindPrev = $em->find('LaPoizWindBundle:DataWindPrev', $id);
				$websiteGetData=WebSiteGetData::getWebSiteObject($dataWindPrev);// return WindguruGetData or MeteoFranceGetData... depend of $dataWindPrev

				$data=$websiteGetData->getDataFromURL($dataWindPrev); // array($result,$chrono)
				$analyse=$websiteGetData->analyseDataFromPage($data[0],$dataWindPrev->getUrl()); // array($result,$chrono)
				
				return $this->container->get('templating')->renderResponse(
							'LaPoizWindBundle:BackOffice/Spot/Ajax:testdataWindPrev.html.twig',
							array(	
								'chrono' => $analyse[1],
								'info' => $analyse[0],
	        					'typeDisplay' => $websiteGetData->typeDisplay($step),
								'step' => $step,
								'dataWindPrev' => $dataWindPrev));
			} else {
				$result="No find DataWindPrev.id=".$id;
			}
		} 	catch (Exception $e) {
			$result=toString($e);
		} 	catch (\ErrorException $ee) {
    		$result=$ee->getMessage();
    	}
		return $this->container->get('templating')->renderResponse(
					'LaPoizWindBundle:BackOffice/Spot/Ajax:testdataWindPrev.html.twig',
					array(	'chrono' => 0,
	        				'step' => 10,
	        				'info' => $result,
							'typeDisplay' => WebsiteGetData::typeDisplay(-1),
							'dataWindPrev' => $dataWindPrev ));
	}
	
	
	/**
	 * @Template()
	 */
	// for testing: http://localhost/WindServer/web/app_dev.php/ajax/dataWindPrev/test/step3/1
	public function testStep3Action($id=null)
	{
		try {
			$message='';
			$em = $this->container->get('doctrine.orm.entity_manager');
			$step=3;

			if (isset($id)) {
				$dataWindPrev = $em->find('LaPoizWindBundle:DataWindPrev', $id);
				$websiteGetData=WebSiteGetData::getWebSiteObject($dataWindPrev);// return WindguruGetData or MeteoFranceGetData... depend of $dataWindPrev

				$data=$websiteGetData->getDataFromURL($dataWindPrev); // array($result,$chrono)
				$analyse=$websiteGetData->analyseDataFromPage($data[0],$dataWindPrev->getUrl()); // array($result,$chrono)
				$transformData=$websiteGetData->transformDataFromTab($analyse[0]); // array($result,$chrono)

				return $this->container->get('templating')->renderResponse(
								'LaPoizWindBundle:BackOffice/Spot/Ajax:testdataWindPrev.html.twig',
								array(
									'chrono' => $transformData[1],
									'info' => $transformData[0],
									'typeDisplay' => $websiteGetData->typeDisplay($step),
									'step' => $step,
									'dataWindPrev' => $dataWindPrev));
			} else {
				$result="No find DataWindPrev.id=".$id;
			}
		} 	catch (Exception $e) {
			$result=toString($e);
		} 	catch (\ErrorException $ee) {
    		$result=$ee->getMessage();
    	}
		return $this->container->get('templating')->renderResponse(
							'LaPoizWindBundle:BackOffice/Spot/Ajax:testdataWindPrev.html.twig',
							array(	'chrono' => 0,
		        				'step' => 10,
		        				'info' => $result,
								'typeDisplay' => WebsiteGetData::typeDisplay(-1),
								'dataWindPrev' => $dataWindPrev ));
	}
	
	
	/**
	 * @Template()
	 */
	// for testing: http://localhost/WindServer/web/app_dev.php/ajax/dataWindPrev/test/save/1
	public function testSaveAction($id=null)
	{
		try {
			$message='';
			$em = $this->container->get('doctrine.orm.entity_manager');
			$step=4;

			if (isset($id)) {
				$dataWindPrev = $em->find('LaPoizWindBundle:DataWindPrev', $id);
				$websiteGetData=WebSiteGetData::getWebSiteObject($dataWindPrev);// return WindguruGetData or MeteoFranceGetData... depend of $dataWindPrev

				$data=$websiteGetData->getDataFromURL($dataWindPrev); // array($result,$chrono)
				$analyse=$websiteGetData->analyseDataFromPage($data[0],$dataWindPrev->getUrl()); // array($result,$chrono)
				$transformData=$websiteGetData->transformDataFromTab($analyse[0]); // array($result,$chrono)
				$saveData=$websiteGetData->saveFromTransformData($transformData[0],$dataWindPrev,$em); // array(array $prevDate,$chrono)

				return $this->container->get('templating')->renderResponse(
								'LaPoizWindBundle:BackOffice/Spot/Ajax:testdataWindPrev.html.twig',
								array(
										'chrono' => $saveData[1],
										'info' => $saveData[0],
										'typeDisplay' => WebsiteGetData::typeDisplay($step),
										'step' => $step,
										'dataWindPrev' => $dataWindPrev));
			} else {
				$result="No find DataWindPrev.id=".$id;
			}
		} 	catch (Exception $e) {
			$result=toString($e);
		} 	catch (\ErrorException $ee) {
    		$result=$ee->getMessage();
    	}
		return $this->container->get('templating')->renderResponse(
								'LaPoizWindBundle:BackOffice/Spot/Ajax:testdataWindPrev.html.twig',
								array(	'chrono' => 0,
			        				'step' => 10,
									'info' => $result,
									'typeDisplay' => WebsiteGetData::typeDisplay(-1),
									'dataWindPrev' => $dataWindPrev ));
	}

	
	/**
	* @Template()
	*/
	// for testing: http://localhost/WindServer/web/app_dev.php/ajax/dataWindPrev/test/remove/previsionDate/{id}
	// remove the previsiondate with id, return -1 if failed, and id if done
	public function removePrevisionDateAction($id=null)
	{
		try {
			$result=-1;
			$em = $this->container->get('doctrine.orm.entity_manager');

			if (isset($id)) {
				$prevDate = $em->find('LaPoizWindBundle:PrevisionDate', $id);
				$em->remove($prevDate);
				$em->flush();
				$result=$id;
			} 
		} 	catch (Exception $e) {
			$result=10;
		} catch (\ErrorException $ee) {
				$result=10;
		}
		
		return $this->container->get('templating')->renderResponse(
										'LaPoizWindBundle:BackOffice/Spot/Ajax:simpleText.html.twig',
										array('text' => $result));
	}


	private function errorReturn($message) {
		return $this->container->get('templating')->renderResponse(
					        		'LaPoizWindBundle:BackOffice/Spot/Ajax:errorText.html.twig',
									array('text' => $message ));
	}

}