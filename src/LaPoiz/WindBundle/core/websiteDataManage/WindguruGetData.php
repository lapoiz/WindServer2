<?php
namespace LaPoiz\WindBundle\core\websiteDataManage;

use LaPoiz\WindBundle\Entity\PrevisionDate;
use LaPoiz\WindBundle\Entity\Prevision;

class WindguruGetData extends WebsiteGetData
{
	
	const exprLineOK = 'var wg_fcst_tab_data_1 = {';
	const exprWindPart = 'WINDSPD';
	const exprWindDirPart = 'WINDDIR';
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

				$windDirPart=WindguruGetData::getPart(WindguruGetData::exprWindDirPart,$line); // get wind direction part in line
				$tableauData['orientation']=WindguruGetData::getElemeInPart($windDirPart);// transforme to tab
  				
  				$hourePart=WindguruGetData::getHourePart($line);				
  				$tableauData['heure']=WindguruGetData::getElemeInPart($hourePart);// transforme to tab
  				
  				$datePart=WindguruGetData::getDatePart($line);				
  				$tableauData['date']=WindguruGetData::getElemeInPart($datePart);// transforme to tab

				$updateTime = WindguruGetData::getSpecialPart(WindguruProGetData::exprUpdateTime, $line);
				$tableauData['update'] = array($updateTime);
  			}
		}
		return $tableauData;
	}
	
	

	function transformData($tableauData) {
		// $tableauData
		// wind  -> 17.5 | 12 | 10 | 14.5 | 15
		// orientation  -> 198 | 172 | 170 | 180 | 188
		// heure -> 13   | 19 | 22 | 01   | 04
		// date  -> 04   | 04 | 04 | 05   | 05
		
		$tableauWindData = array();
		$currentDate = '';
		$firstElem=true;
		$currenteLine=array();
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
            if (isset($tableauData['orientation'][$key])) {
                $dataPrev["orientation"] = WebsiteGetData::transformeOrientationDegToNom($tableauData['orientation'][$key]);
            } else {
                $dataPrev["orientation"] = '?';
            }
			$currenteLine[$tableauData['heure'][$key]]=$dataPrev;
			$currentDate=$date;
			//$indexCol++;
		}
		$tableauWindData[WindguruGetData::getCompleteDate($currentDate)]=$currenteLine;
		$tableauWindData['update']=array(array(WindguruGetData::transformeUpdate($tableauData['update'][0])));
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
      // la valeur peut parfoit être égale à "null"
      // $patternPart = '#\"'.$expres.'\":\[([\d\.,\"]*)#'; -> fonctionne hors "null"
      $patternPart = '#\"'.$expres.'\":\[([\d|null\.,\"]*)#';
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
            $today->modify( '-2 day' );// if we do that late, and prevision of yesterday still here...
            if ($today->format('d') > $date) {
			    $today->modify( '+1 month' );
            }
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

	/**
	 * Recupere élément seul et non un tableau de données, ex: ,"update_last":"Sun, 13 Dec 2015 17:21:16 +0000",
	 */
	static private function getSpecialPart($expres,$line) {
		// \"update_last\":\"([\w,\s:+]+)\"
		$patternPart = '#\"'.$expres.'\":\"([\w,\s:+]+)\"#';
		preg_match_all($patternPart,$line,$parts);
		if (count($parts[1])>0) {
			return $parts[1][0];
		} else {
			return null;
		}
	}

	/*
	 * $htmlUpdate: "Sun, 13 Dec 2015 17:21:16 +0000"
	 * return : 2015-12-13 18:21:16
	 */
	private function transformeUpdate($htmlUpdate) {
		//preg_match('#(\w+), (\d+) (\w+) (\d+) (\d+):(\d+):(\d+)#/i',$htmlUpdate,$data);
		//preg_match('#(\w|,|\s|\d|:)+#',$htmlUpdate,$data);
		// on a séparé chque groupe d'element, il faut juste transformer le mois
		$date=strtotime($htmlUpdate);
		$result=date('Y-m-d H:i:s',$date);
		return $result;
	}

}