<?php
namespace LaPoiz\WindBundle\core\websiteDataManage;

use LaPoiz\WindBundle\Entity\PrevisionDate;
use LaPoiz\WindBundle\Entity\Prevision;

class WindguruGetData extends WebsiteGetData
{
	
	const exprLineOK = 'var wg_fcst_tab_data_1 = {';
	const exprWindPart = 'WINDSPD';
	const windguruFilePageName='windguruPage.txt';
  
	
	function getDataURL($url) 
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
		
	function analyseData($tabData,$url) {
		$tableauData = array();	
		foreach ($tabData as $line) {
  			if (WindguruGetData::isGoodLine($line)) {  // choose good line where every data is			    
  				$windPart=WindguruGetData::getPart(WindguruGetData::exprWindPart,$line); // get wind part in line				
  				$tableauData['wind']=WindguruGetData::getElemeInPart($windPart);// transforme to tab		 		
  				
  				$hourePart=WindguruGetData::getHourePart($line);				
  				$tableauData['heure']=WindguruGetData::getElemeInPart($hourePart);// transforme to tab
  				
  				$datePart=WindguruGetData::getDatePart($line);				
  				$tableauData['date']=WindguruGetData::getElemeInPart($datePart);// transforme to tab	
  			}
		}
		return $tableauData;
	}
	
	

	function transformData($tableauData) {
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
					$tableauWindData[WindguruGetData::getCompleteDate($currentDate)]=$currenteLine;
				}
				$currenteLine=array();
			}
			$dataPrev=array();
			$dataPrev["wind"]=$tableauData['wind'][$key];
			$dataPrev["heure"]=$tableauData['heure'][$key];
			$dataPrev["orientation"]="?";
			$currenteLine[$tableauData['heure'][$key]]=$dataPrev;
			$currentDate=$date;
			//$indexCol++;
		}
		$tableauWindData[WindguruGetData::getCompleteDate($currentDate)]=$currenteLine;
		return $tableauWindData;
	}
		
	
	/**
	 * 
	 * Main function where is decide if the line is one of the good lines for wind data 
	 * @param  $line: line from HTML page
	 */
	static private function isGoodLine($line) {
	  // line begine with WindguruGetData::exprLineOK
		//$pattern = '/^'.WindguruGetData::exprLineOK.'/';
		$pattern = '/'.WindguruGetData::exprLineOK.'/';
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
			//next month
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