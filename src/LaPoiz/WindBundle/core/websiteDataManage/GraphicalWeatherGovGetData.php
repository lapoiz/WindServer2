<?php
namespace LaPoiz\WindBundle\core\websiteDataManage;

use LaPoiz\WindBundle\Entity\PrevisionDate;
use LaPoiz\WindBundle\Entity\Prevision;


class GraphicalWeatherGovGetData
{
	
	static buildURL($latitude, $longitude) {
		$today=new \DateTime("now");
		$url = "http://graphical.weather.gov/xml/SOAP_server/ndfdXMLclient.php?whichClient=NDFDgen&";
		$url .= "lat=".$latitude;
		$url .= "&lon=".$longitude;
		$url .= "&listLatLon=&lat1=&lon1=&lat2=&lon2=&resolutionSub=&listLat1=&listLon1=&listLat2=&listLon2=&resolutionList=&endPoint1Lat=&endPoint1Lon=&endPoint2Lat=&endPoint2Lon=&listEndPoint1Lat=&listEndPoint1Lon=&listEndPoint2Lat=&listEndPoint2Lon=&zipCodeList=&listZipCodeList=&centerPointLat=&centerPointLon=&distanceLat=&distanceLon=&resolutionSquare=&listCenterPointLat=&listCenterPointLon=&listDistanceLat=&listDistanceLon=&listResolutionSquare=&citiesLevel=&listCitiesLevel=&sector=&gmlListLatLon=&featureType=&requestedTime=&startTime=&endTime=&compType=&propertyName=&product=time-series&";
		$url .= "begin=".$today->format('Y-m-d');//2012-01-21
		$url .= "T00%3A00%3A00&";
		$url .= "end=".$today->format('Y-m-d', strtotime("+1 weeks"));//2012-01-28
		$url .= "T00%3A00%3A00&Unit=e&wspd=wspd&wdir=wdir&wx=wx&waveh=waveh&appt=appt&wgust=wgust&Submit=Submit";
	}
	
	static function getDataURL($url) 
	{         
	    $tableauData = array();
		$file = fopen($url,"r");		
		
		while (!feof($file)) 
		{ // line per line			
  			$line = fgets($file); // read a line
  			$result[]=$line;
		}		
		fclose($file);
		return $result; 		
	}
	
	static function parseURL($url) {
		$dom = new DOMDocument();
		if (!$dom->load($url)) {
		    die("Impossible de charger le fichier XML");
		    //TODO: gestion d'erreur
		}
		$tableauData = array();
		$windSpeed = $dom->getElementsByTagName("wind-speed");
		$listWindSpeed = $windSpeed->getElementsByTagName('value')
		foreach ($listWindSpeed as windSpeedValue) {
			$tableauData[]=windSpeedValue->nodeValue;
		}
	}
	
	/**
	 * 
	 * @param page URL $url
	 */
	static function tableauData($url) 
	{ 
		$tableauData = array();
		$file = fopen($url,"r");		
		
		while (!feof($file)) 
		{ // line per line			
  			$line = fgets($file); // read a line
  			
  			if (WindguruGetData::isGoodLine($line)) {  // choose good line where every data is			    
  				$windPart=WindguruGetData::getPart(WindguruGetData::exprWindPart,$line); // get wind part in line				
  				$tableauData['wind']=WindguruGetData::getElemeInPart($windPart);// transforme to tab		 		
  				
  				$hourePart=WindguruGetData::getHourePart($line);				
  				$tableauData['heure']=WindguruGetData::getElemeInPart($hourePart);// transforme to tab
  				
  				$datePart=WindguruGetData::getDatePart($line);				
  				$tableauData['date']=WindguruGetData::getElemeInPart($datePart);// transforme to tab		 		
  				
  			}
		}		
		fclose($file);
		return $tableauData;
	}

	static function transformInCorectArray($tableauData) {
		// $tableauData
		// wind  -> 17.5 | 12 | 10 | 14.5 | 15
		// heure -> 13   | 19 | 22 | 01   | 04
		// date  -> 04   | 04 | 04 | 05   | 05
		
		$tableauWindData = array();
		$currentDate = '';
		$firstElem=true;
		$currenteLine;
		//$indexCol=0;
		
		foreach ($tableauData['date'] as $key=>$date) {
			if ($currentDate!=$date) {
				if ($firstElem) {
					$firstElem=false;
				} else {
					$tableauWindData[WindguruGetData::getCompleteDate($date)]=$currenteLine;
				}
				$currenteLine=array();
			}
			$currenteLine[$tableauData['heure'][$key]]=$tableauData['wind'][$key];
			$currentDate=$date;
			//$indexCol++;
		}
		$tableauWindData[WindguruGetData::getCompleteDate($currentDate)]=$currenteLine;
		return $tableauWindData;
	}
	
	
	static function saveDataFromArray($tableauData,$dataWindPrev,$entityManager){
		
		// $tableauData
		// 2011-12-05 -> 13=>17.5 | 19=>12 | 22=>10 
		// 2011-12-06 -> 01=>14.5 | 04=>15 ...		
		$now=new \DateTime("now");
	  	foreach ($tableauData as $date=>$lineWindData) {
	  		$prevDate = new PrevisionDate();
	  		$prevDate->setCreated($now);
	  	
		  	$prevDate->setDatePrev(new \DateTime($date));
		  	
		  	$windCalculate= array("max"=>0,"min"=>0,"cumul"=>0,"nbPrev"=>0);
		  	
		  	foreach ($lineWindData as $heure=>$wind) {
		  		$prev = new Prevision();
		  		$prev->setOrientation('?');
		  		$prev->setWind($wind);
		  		$hour=new \DateTime();
		  		$hour->setTime($heure, "00");
		  		$prev->setTime($hour);
		  		
		  		WindguruGetData::calculateWind($windCalculate,$prev);
		  		
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
		 }
		 $entityManager->persist($dataWindPrev);
		 $entityManager->flush();
	} 
	
	/**
	 * 
	 * Main function where is decide if the line is one of the good lines for wind data 
	 * @param  $line: line from HTML page
	 */
	static private function isGoodLine($line) {
	  // line begine with WindguruGetData::exprLineOK
		$pattern = '/^'.WindguruGetData::exprLineOK.'/';
		return preg_match($pattern,$line)>0;
	}
	static private function getElemeInPart($windPart) 
	{	 		
		return preg_split('/,/',$windPart);
	}
	
    static private function getPart($expres,$line) 
	{ 
      //$patternPart = '#\"WINDSPD\":\[([\d\.,\"]*)#';
      $patternPart = '#\"'.$expres.'\":\[([\d\.,\"]*)#';
	  preg_match_all($patternPart,$line,$parts);
      return $parts[1][0];
	}

	//need special with hr_d can't be send in param of a function...
    static private function getDatePart($line) 
	{
      $patternPart = '#\"hr\_d\":\[([\d\.,\"]*)#';
      //$patternPart = '#"hr_d":\[([0-9\.,"]+)\]#';
	  preg_match_all($patternPart,$line,$parts);
	  $result= preg_replace('#\"#','',$parts[1][0]);
      return $result;
	}

	// need special with hr_h can't be send in param of a function...
    static private function getHourePart($line) 
	{
      $patternPart = '#\"hr\_h\":\[([\d\.,\"]*)#';
	  preg_match_all($patternPart,$line,$parts);
	  $result= preg_replace('#\"#','',$parts[1][0]);
      return $result;
	}
	
	/**
	 * transforme date like '15' in saved date : '05/12/2011'
	 * @param string $date
	 */
	static private function getCompleteDate($date) {
		$today= new \DateTime("now");
		if ($today->format('d') > $date) {
			//nest month
			$today->modify( '+1 month' );			
		}
		$result=$today->format('Y-m-').$date;
		return $result;
	}
	
	/**
	 * $windCalculate -> max | min | cumul | nbPrev
	 */
	static function calculateWind($windCalculate, $prevision) {
		$wind=$prevision->getWind();
		$windCalculate["max"]=($wind>$windCalculate["max"]?$wind:$windCalculate["max"]);
		$windCalculate["min"]=($wind<$windCalculate["min"]?$wind:$windCalculate["min"]);
		$windCalculate["cumul"]+=$wind;
		$windCalculate["nbPrev"]++;
	}
	
	/*
	 * 3627|3|1281931200|169|'France - Saint-Aubin-sur-Mer'|'16.08. 2010 06'|1|0|2|23|46|49.8768|0.8025
	 */
	private function getDateFromHTML($htmlLine) {
		preg_match('#([0-9]{2}).([0-9]{2}).\s([0-9]{4})\s#',$htmlLine[5],$data);
		return $data[3].'-'.$data[2].'-'.$data[1];
	}
}