<?php
namespace LaPoiz\WindBundle\core\websiteDataManage;

use LaPoiz\WindBundle\Entity\PrevisionDate;
use LaPoiz\WindBundle\Entity\Prevision;

class WindFinderGetData extends WebsiteGetData
{
	const idHTML = 'tabs';
	const divClass= 'tab-content';
	const nbLine = 4;
	const dataClassName = 'weathertable';
	const dataClassName2 = 'weathertable last-weathertable';


	static private function getWichColumTab($htmlTabData) {
		
		$result1 = array(
			"nbLineTabPerLineWeb"=>22,
			"time"=>1,
			"wind"=>5,
			"maxWind"=>7,
			"temp"=>21,
			"orientation"=>3
		);
		$result2 = array(
			"nbLineTabPerLineWeb"=>16,
			"time"=>1,
			"wind"=>5,
			"maxWind"=>7,
			"temp"=>15,
			"orientation"=>3
		);
		$nbCol=count($htmlTabData);
		if ((($nbCol)/$result1["nbLineTabPerLineWeb"])==4) 
			return $result1;
		else
			return $result2; 
	}

	function getDataURL($url)
	{
		$tableauData = array();
		$file = fopen($url,"r");
		
		while (!feof($file)) 
		{
		// line per line
			$line = fgets($file); // read a line
			$result[]=$line;
		}
		fclose($file);
		return $result;
	}
	
	function analyseData($pageHTML,$url) {
		
		$dom = new \DOMDocument();
		@$dom->loadHTMLFile($url);
		//$dom->save('windfinderPageExemple.html');
		
		$tableauData = array();	
		if (empty($dom)) {
			return null;
		} else	{
			//$div = $dom->getElementById(WindFinderGetData::idHTML);
			$div=WindFinderGetData::getGoodDiv($dom);	
			if (empty($div)){
				echo '<br />div not find is id :'.WindFinderGetData::idHTML.' correct ?<br /><';
			} else {
				$tables = $div->getElementsByTagName('table'); // get all tables of the div
				foreach ($tables as $table){
					// for each table of the div
					if ($table->getAttribute('class')==WindFinderGetData::dataClassName || $table->getAttribute('class')==WindFinderGetData::dataClassName2) {
						$rows = $table->getElementsByTagName('tr');
						foreach ($rows as $row){
							// for each col of the table
							$line = WindFinderGetData::getElemeLine('th',$row);//get each cell in array
							if (!empty($line))
								$tableauData[] = $line;
							$line = WindFinderGetData::getElemeLine('td',$row);//get each cell in array
							if (!empty($line))
								$tableauData[] = $line;
						}
					}
				}
			}
		}
		return $tableauData;
	}
	
	function transformData($htmlTabData) {
		$cleanTabData = array();
		$whichColumn = WindFinderGetData::getWichColumTab($htmlTabData);
		
		$today = WindFinderGetData::getTodayFromHTML($htmlTabData);
		$nbLineTabPerLineWeb = $whichColumn["nbLineTabPerLineWeb"];
		$timeCol = $whichColumn["time"];

		for ($numLine=0;$numLine<(WindFinderGetData::nbLine);$numLine++) {
			$nbCol=count($htmlTabData[$numLine*$nbLineTabPerLineWeb+$timeCol])-1;
			$prevHoure=WindFinderGetData::getTimePrevFromHTML($htmlTabData[$numLine*$nbLineTabPerLineWeb+$timeCol][1]);
			$datePrev = WindFinderGetData::getDatePrevFromHTML($htmlTabData[$numLine*$nbLineTabPerLineWeb][1]);
			for ($numCol=0;$numCol<$nbCol;$numCol++) {
				$timePrev = WindFinderGetData::getTimePrevFromHTML($htmlTabData[$numLine*$nbLineTabPerLineWeb+$timeCol][$numCol+1]);
				//echo '<br/>$timePrev:'.$timePrev.'  $prevHoure:'.$prevHoure.'  $datePrev:'.$datePrev.'  $numLine'.$numLine.'   $numCol:'.$numCol;
				if ($timePrev<$prevHoure) {
					// new day
					$datePrev = WindFinderGetData::getDatePrevFromHTML($htmlTabData[$numLine*$nbLineTabPerLineWeb][2]);
				}
				$lineData = WindFinderGetData::getWindFinderHtmlData($htmlTabData,$whichColumn,$numLine,$numCol,$today,$datePrev,$timePrev);
				//$prevHoure=$lineData['timePrev'];
				$prevHoure=$lineData['heure'];
				
				$cleanTabData[$datePrev][] = $lineData;
			}
		}
		return $cleanTabData;
	}
	
	// New
	
	// find the div where table of data is
	static private function getGoodDiv($dom) {
		$divs = $dom->getElementsByTagName('div');
		$divFind=null;
		foreach ($divs as $div) {
			if ($div -> getAttribute('class')==WindFinderGetData::divClass) {
				$divFind=$div;
			}
		}
		return $divFind;
	}
	
	static private function getElemeLine($tag,$row)
	{
		$rowTab = array();
		$cols = $row->getElementsByTagName($tag);
		foreach ($cols as $cell) {
			$value = $cell->nodeValue;
			$value=preg_replace('/\s\s+/', '', $value);
			$rowTab[] = $cell->nodeValue;
	
		}
		return $rowTab;
	}
	
	static private function getWindFinderHtmlData($htmlTabData,$whichColumn,$numLine,$numCol,$today,$datePrev,$timePrev) {
		$nbLineTabPerLineWeb=$whichColumn["nbLineTabPerLineWeb"];
		$colClean = array();
		//$colClean['date'] = $today;
		//$colClean['datePrev'] = $datePrev;
		$colClean['date'] = $datePrev;
		$colClean['heure'] = $timePrev;
		$colClean['wind'] = WindFinderGetData::getWindPrevFromHTML($htmlTabData[$numLine*$nbLineTabPerLineWeb+$whichColumn["wind"]][$numCol]);//corection orientation
		$colClean['maxWind'] = WindFinderGetData::getMaxWindPrevFromHTML($htmlTabData[$numLine*$nbLineTabPerLineWeb+$whichColumn["maxWind"]][$numCol]);
		$colClean['temp'] = WindFinderGetData::getTempPrevFromHTML($htmlTabData[$numLine*$nbLineTabPerLineWeb+$whichColumn["temp"]][$numCol]);
		$colClean['orientation'] = WindFinderGetData::getOrientationPrevFromHTML($htmlTabData[$numLine*$nbLineTabPerLineWeb+$whichColumn["orientation"]][$numCol]);
		return $colClean;
	}
	

	static private function getTodayFromHTML($htmlData) {
		return date("Y-m-d");
	}
	// Tuesday,� Aug� 17	
	static private function getDatePrevFromHTML($htmlData) {
		//$this->get('logger')->err('htmlDate:'+$htmlData);
        $htmlData = trim($htmlData);
		preg_match('#([0-9]{2})$#',$htmlData,$data);
		$test=date('d');
		$test='';
		if ($data[1]>=date('d'))
			return date("Y-m-").$data[1];
		elseif (date('m')<=11)
			return date("Y-").(date('m')+1).'-'.$data[1];
		else
			return (date("Y")+1).'-01-'.$data[1];
	}

	
	// 08h
	static private function getTimePrevFromHTML($htmlData) {
		preg_match('#([0-9]{2})h#',$htmlData,$data);
		return $data[1];
	}
	static private function getWindPrevFromHTML($htmlData) {
		if (preg_match('#[0-9]+#',$htmlData,$data)>0) {
			return $data[0];
		} else {
			return "?";
		}
	}
	static private function getMaxWindPrevFromHTML($htmlData) {
		if (preg_match('#[0-9]+#',$htmlData,$data)>0) {
			return $data[0];
		} else {
			return "?";
		}
	}
	static private function getTempPrevFromHTML($htmlData) {
		if (preg_match('#[0-9]+#',$htmlData,$data)>0) {
			return $data[0];
		} else {
			return "?";
		}
	}
	
	static private function getOrientationPrevFromHTML($htmlData) {
		if (preg_match('#[nsew]+#',$htmlData,$data)>0) {
			return $data[0];
		} else { 
			return "?";
		}
	}
	
	
	// Delete	
	

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