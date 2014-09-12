<?php
namespace LaPoiz\WindBundle\core\websiteDataManage;

use LaPoiz\WindBundle\Entity\PrevisionDate;
use LaPoiz\WindBundle\Entity\Prevision;


class WebsiteGetData
{
	const windguruName="Windguru";
	const windFinderName="WindFinder";
	const meteoFranceName="MeteoFrance"; 
	
	static function getListWebsiteAvailable() {
		return array(
			WebsiteGetData::meteoFranceName,
			WebsiteGetData::windguruName,
			WebsiteGetData::windFinderName 
		);
	}

	static function getWebSiteObject($dataWindPrev) {
		$result=null;
		switch ($dataWindPrev->getWebsite()->getNom()) {
			case WebsiteGetData::meteoFranceName:
				return new MeteoFranceGetData();
				break;
			case WebsiteGetData::windguruName:
				return new WindguruGetData();
				break;
			case WebsiteGetData::windFinderName:
				return new WindFinderGetData();
				break;
		}
		return $result;
	}
	
	 function getDataURL($url) { return " mistake, is not good object";} 
	 function analyseData($tabData,$url) {return " mistake, is not good object";} 
	 function transformData($tableauData) {return " mistake, is not good object";}
	
	 function getAndCleanData($dataWindPrev) {
		$url=$dataWindPrev->getUrl();
		$step=0;		
		$start_time=microtime(true); // top chrono
		
		try {
			$result=$this->getDataURL($url);
			$step=1;
			$result=$this->analyseData($result,$url);
			$step=2;
			$result=$this->transformData($result);
			$step=3;
		} 	catch (Exception $e) {
        	$result = toString($e);
    	}
    	
    	$chrono=microtime(true)-$start_time;
    	return array($step,$result,$chrono);
	}
	
	
	// return page of URL
	 function getDataFromURL($dataWindPrev) 
	{ 
		$url=$dataWindPrev->getUrl();
		// top chrono
		$start_time=microtime(true);
		// get data from son function
		$result=$this->getDataURL($url);
		// stop chrono
		$chrono=microtime(true)-$start_time;
		
		return array($result,$chrono);
	}

	
	// return analyse of page (getDataFromURL)
	function analyseDataFromPage($data,$url)
	{
		// top chrono
		$start_time=microtime(true);
		// get data from son function
		$result=$this->analyseData($data,$url);
		// stop chrono
		$chrono=microtime(true)-$start_time;

		return array($result,$chrono);
	}
	
	// return transforme tab from analyse data (analyseDataFromPage)
	function transformDataFromTab($analyseData)
	{
		// top chrono
		$start_time=microtime(true);
		// get data from son function
		$result=$this->transformData($analyseData);
		// stop chrono
		$chrono=microtime(true)-$start_time;

		return array($result,$chrono);
	}
	
	// save data from transforme data (transformDataFromTab) and return array of $prevDate and chrono 
	function saveFromTransformData($transformData,$dataWindPrev,$entityManager)
	{
		// top chrono
		$start_time=microtime(true);
		// get data from son function
		$result=$this->saveData($transformData,$dataWindPrev,$entityManager); // return array of $prevDate
		// stop chrono
		$chrono=microtime(true)-$start_time;

		return array($result,$chrono);
	}
	
	function saveData($tableauData,$dataWindPrev,$entityManager){
		// $tableauData
		// 2011-12-05 -> 13=>[wind=>17.5|orientation=>NNO...] | 19=>[wind=>12|orientation=>NO...] | 22=>...
		$now=new \DateTime("now");
		$result= array();
		foreach ($tableauData as $date=>$lineWindData) {
			$prevDate = new PrevisionDate();
			$prevDate->setCreated($now);
			
			$prevDate->setDatePrev(new \DateTime($date));
			 
			$windCalculate= array("max"=>0,"min"=>0,"cumul"=>0,"nbPrev"=>0);
		 
			foreach ($lineWindData as $dataPrev) {
				$prev = new Prevision();
				$prev->setOrientation($dataPrev["orientation"]);
				$prev->setWind($dataPrev["wind"]);
				$hour=new \DateTime();
				if (isset($dataPrev["heure"]) && !empty($dataPrev["heure"]) && strlen($dataPrev["heure"])>0) {
					$hour->setTime($dataPrev["heure"], "00");
				} else {
					$hour->setTime("01", "00");
				}
				$prev->setTime($hour);
				
				WindFinderGetData::calculateWind($windCalculate,$prev);
				
				$prev->setPrevisionDate($prevDate);
				$prevDate->addListPrevision($prev);
				$entityManager->persist($prev);
			}
		 
			//TODO: calculate average etc...
			$prevDate->setWindAverage(0);
			if ($windCalculate["nbPrev"]>0)
			$prevDate->setWindAverage($windCalculate["max"]/$windCalculate["nbPrev"]);
			$prevDate->setWindMax($windCalculate["max"]);
			$prevDate->setWindMin($windCalculate["min"]);
			$prevDate->setWindGauss(0);
			$prevDate->setWindMiddle(0);
			 
			$prevDate->setDataWindPrev($dataWindPrev);
			$dataWindPrev->addListPrevisionDate($prevDate);
			 
			$entityManager->persist($prevDate);
			$result[]=$prevDate;
		}
		$entityManager->persist($dataWindPrev);
		$entityManager->flush();
		return $result;
	}
	
	static function transformeKmhByNoeud($windKmh) {
		return round($windKmh/1.852,0);
	}

	static function transformeOrientation($orientation) {
		$result='';

		switch ($orientation) {
			case 'nord':
				$result='n';
				break;

			case 'sud':
				$result='s';
				break;

			case 'ouest':
				$result='w';
				break;

			case 'est':
				$result='e';
				break;
		}
		return $result;
	}


}