<?php
namespace LaPoiz\WindBundle\core\websiteDataManage;

use LaPoiz\WindBundle\Entity\PrevisionDate;
use LaPoiz\WindBundle\Entity\Prevision;

class MeteoFranceGetData extends WebsiteGetData
{
	const goodDivClass= 'mod-previsions-affichage clearfix';
	const dayDivClass= 'bloc-day-summary ';
	const dayDivClass2= 'bloc-day-summary first active';
	const detailDayDivId= 'ajax-day-detail';
	const detailDateHoureDivClass= 'bloc-day-summary';
	const tempDivClass= 'day-summary-temperature';
	const windDivClass= 'day-summary-wind-info';
	const windSpanClass= 'vent-detail-vitesse';
	const windMaxSpanClass= 'vent-detail-type';
	const orientationSpanClass= 'picVent *';
	const prefixIddetailDayDiv= 'detail-';


	const tempClass= 'minmax';
	const windClass= 'vents';
	//const detailDdClass= 'detail';
	const detailDivClass= 'bloc_details';


	function getDataURL($url) 
	{
	    		
		$ch = \curl_init();
		$user_agent = "Mozilla/5.0 (X11; U; Linux x86_64; fr; rv:1.9.0.1) Gecko/2008072820 Firefox/3.0.1";
		
		curl_setopt($ch, CURLOPT_URL, $url);
  		curl_setopt($ch, CURLOPT_HEADER, 1); 
	  	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	  	curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
	  	curl_setopt($ch, CURLOPT_COOKIEJAR, "cookies.txt");
        curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
        
	  	$html = curl_exec($ch);
		curl_close($ch);
		$dom = new \DomDocument();
		@$dom->loadHTML($html);
		//$dom->save('../web/tmp/meteoFrancePage0.txt');

		return $html; 		 		
	}

	function analyseData($pageHTML,$url) {
		
		$dom = new \DOMDocument();
		@$dom->loadHTML($pageHTML);
		//$dom->save('../web/tmp/meteoFrancePage.html');
		
		$tableauData = array();	
		$tableauDataResult = array();	
		if (empty($dom)) {
			return null;
		} else	{
			$divAll=MeteoFranceGetData::getGoodDiv($dom);	
			if (empty($divAll)){
				echo '<br />Element not find is div class="'.MeteoFranceGetData::goodDivClass.'" ... correct ?<br /><';
			} else {
				$days = $divAll->getElementsByTagName('div'); // get all days

				foreach ($days as $day){
					$detailDivClass = $day->getAttribute('class');
					if ($detailDivClass==MeteoFranceGetData::dayDivClass or $detailDivClass==MeteoFranceGetData::dayDivClass2) {
						$dateId = MeteoFranceGetData::getDateId($day);
						/*
							<div class="loading"> <div class="img"/> </div> 
							<div class="box"> 
								<div class="box-header"> 
									<h3 class="day-summary-title">Aujourd'hui</h3> 
								</div> 
								<div class="box-body"> 
									...
								</div> 
							</div> 
						*/
						$date= MeteoFranceGetData::getDate($day);
						
						$previsionTab['date'] = $date;
						$previsionTab['dateId'] = $dateId;
						$tableauData[]=$previsionTab;
					}
				}
				$detailDiv = MeteoFranceGetData::getDetailDiv($dom);

				foreach ($tableauData as $previsionTab) {
					$detailDateDiv = MeteoFranceGetData::getDetailDateDiv($dom,$previsionTab['dateId']);
					
					foreach ($detailDateDiv->getElementsByTagName('div') as $detailDateHoureDiv) {
						/* 			 
 				<div class="bloc-day-summary"> 
 					<div class="box"> 
 						<div class="box-header"> 
 							<h4 class="day-summary-title">Matin</h4> 
 						</div> 
 						<div class="box-body"> 
 							... 
 						</div> 
 					</div> 
 				</div> 
 				...  			
					*/
							$detailDateHoureDivClass = $detailDateHoureDiv->getAttribute('class');
							if ($detailDateHoureDivClass==MeteoFranceGetData::detailDateHoureDivClass) {
								
					/* 			 
 						<div class="box-header"> 
 							<h4 class="day-summary-title">Matin</h4> 
 						</div> 
 						<div class="box-body"> 
 							<div class="day-summary-temperature"> 12°C | <strong>13°C</strong> </div> 
 							<div class="day-summary-ressentie"> Ressentie 10 °C | <strong>11 °C</strong> </div> 
 							<div class="day-summary-image"> <span class="picTemps J_W1_12-N_3">Pluies orageuses</span> </div> 
 							<div class="day-summary-broad">Pluies orageuses</div> 
 							<div class="day-summary-wind"> 
 								<div class="day-summary-wind-info"> 
 									<span class="picVent V_E">Vent est</span> 
 									<span class="vent-detail-vitesse">Vent 18km/h</span> 
 									<span class="vent-detail-type"/> 
 								</div> 
 							</div> 
 						</div> 
					*/
	 							$time='';
								$temp='';
								$wind='';
								$windMax='';
								$orientation='';

								$time = MeteoFranceGetData::getTime($detailDateHoureDiv);
								$divs = $detailDateHoureDiv->getElementsByTagName('div');					
								foreach ($divs as $div) {
									if ($div -> getAttribute('class')==MeteoFranceGetData::tempDivClass) {
										$temp=$div->nodeValue;
									} elseif ($div -> getAttribute('class')==MeteoFranceGetData::windDivClass) {
										$orientation=$div->getElementsByTagName('span')->item(0)->nodeValue;
									} 

								} 
								$spans = $detailDateHoureDiv->getElementsByTagName('span');					
								foreach ($spans as $span) {
									if ($span -> getAttribute('class')==MeteoFranceGetData::windSpanClass) {
										$wind=$span->nodeValue;
									} elseif ($span -> getAttribute('class')==MeteoFranceGetData::orientationSpanClass) {
										$orientation=$span->nodeValue;									
									} elseif ($span -> getAttribute('class')==MeteoFranceGetData::windMaxSpanClass) {
										$windMax=$span->nodeValue;
									} 
								}
								$previsionTab['time'] = $time;
								$previsionTab['temp'] = $temp;
								$previsionTab['wind'] = $wind;
								$previsionTab['windMax'] = $windMax;
								$previsionTab['orientation'] = $orientation;
								$tableauDataResult[] = $previsionTab;
							}//if
					}//for		
				}//for	
				
			}// else
		}// else
		return $tableauDataResult;
	}

	function transformData($tableauData) {
		$cleanTabData = array();
		$datePrev = '';
		$date = '';
		$cleanLineTab = array();
		$isFirstElem=true;

		foreach ($tableauData as $lineData) {
			$date = MeteoFranceGetData::getDateClean($lineData['date']);

			if ($datePrev!=$date && !$isFirstElem) {
				$cleanTabData[$datePrev]=$cleanLineTab;
				$cleanLineTab = array();
				$datePrev=$date;
			}

			$cleanElemTab = array();			
			$cleanElemTab['heure'] = MeteoFranceGetData::getHoureClean($lineData['time']);
			$cleanElemTab['date'] = $date;
			$cleanElemTab['wind'] = MeteoFranceGetData::getWindClean($lineData['wind']);
			$cleanElemTab['maxWind'] = MeteoFranceGetData::getMaxWindClean($lineData['windMax']);
			$cleanElemTab['temp'] = MeteoFranceGetData::getTempClean($lineData['temp']);
			$cleanElemTab['orientation'] = MeteoFranceGetData::getOrientationClean($lineData['orientation']);
			$cleanLineTab[] = $cleanElemTab;

			if ($isFirstElem) {
				$isFirstElem = false;
				$datePrev=$date;			
			}
			$cleanTabData[$date]=$cleanLineTab;
			
		}

		return $cleanTabData;
	}


	// find the div where table of data is
	static private function getGoodDiv($dom) {
		$divs = $dom->getElementsByTagName('div');
		$divFind=null;
		foreach ($divs as $div) {
			if ($div -> getAttribute('class')==MeteoFranceGetData::goodDivClass) {
				$divFind=$div;
			}
		}
		return $divFind;
	}

	
	/*
		<div class="bloc-day-summary first active" id="day-symmary-id-00001"> 
	*/
	static private function getDateId($day) {
		return $day->getAttribute('id');
	}

	// find the value of the date in the h3
	/*
		<h3 class="day-summary-title">Aujourd'hui</h3>
		ou
		<h3 class="day-summary-title">samedi 18</h3> 
	*/
	static private function getDate($day) {
		return $day->getElementsByTagName('h3')->item(0)->nodeValue;
	}

	/*
		<h4 class="day-summary-title">Matin</h4> 
	*/
	static private function getTime($detailDateHoureDiv) {
		return $detailDateHoureDiv->getElementsByTagName('h4')->item(0)->nodeValue;
	}

	/*	
		<div class="group-day-detail hide-id-js" id="detail-day-symmary-id-00002" style="display: none;"> 
	*/
	static private function getDetailDiv($div) {
		return $div->getElementById(MeteoFranceGetData::detailDayDivId);
	}

	/*	
		<div class="group-day-detail hide-id-js" id="detail-day-symmary-id-00002" style="display: none;"> 
	*/
	static private function getDetailDateDiv($div,$dateId) {
		return $div->getElementById(MeteoFranceGetData::prefixIddetailDayDiv.$dateId);
	}

			






	// input: Aujourd'hui / Mardi 17
	// return: 2013-09-16
	static private function getDateClean($htmlValue) {
		$today= new \DateTime("now");
		if (preg_match('#^Aujourd#',$htmlValue)) {
			return $today->format('Y-m-d');	
		} else {
			preg_match('#^(?<day>\w+)\s?(?<date>[0-9]+)#',$htmlValue,$value);
			$date = $value['date'];

			if ($today->format('d') > $date) {
				//next month
				$today->modify( '+1 month' );			
			}
			return $today->format('Y-m-').$date;

		}
	}

	// input:  Nuit / Matin / AprÃ¨s-midi / SoirÃ©e 
	// return: 15
	static private function getHoureClean($htmlValue) {
		if (preg_match('#^Nuit#',$htmlValue)) {
			return 23;
		} elseif (preg_match('#^Matin#',$htmlValue)) {
			return 10;
		} elseif (preg_match('#^Apr#',$htmlValue)) {
			return 16;
		} elseif (preg_match('#^Soir#',$htmlValue)) {
			return 20;
		}
		return $htmlValue;
	}

	// input: 15Â°C   ou 12°C | 13°C
	// return: 15
	static private function getTempClean($htmlValue) {
		if (preg_match('#[0-9]+#',$htmlValue,$value)>0) {
			return $value[0];
		} else {
			return "?";
		}
	}

	// input: Vent 18km/h
	// return: 13
	static private function getWindClean($htmlValue) {
		if (preg_match('#^Vent (?<wind>[0-9]+)#',$htmlValue,$value)>0) {
			$wind = $value['wind'];
			//return $wind;
			return WebsiteGetData::transformeKmhByNoeud($wind);
		} else {
			return "?";
		}
	}

	// input: Vent ouest-nord-ouest
	// return: wnw
	static private function getOrientationClean($htmlValue) {
		if (preg_match('#^Vent (?<orientation1>\w+)\-?(?<orientation2>\w*)\-?(?<orientation3>\w*)#',$htmlValue,$value)>0) 
		{
			//if (preg_match('#^Vent (\w+)+\-?(\w*)*\-?(\w*)*#',$htmlValue,$value)>0) {
			$orientation = WebsiteGetData::transformeOrientation($value['orientation1']);
			if (sizeof($value)>2) {
				//$orientation .= ' '.$value[3];
				$orientation .= WebsiteGetData::transformeOrientation($value['orientation2']);
			} 
			if (sizeof($value)>3) {
				//$orientation .= ' '.$value[3];
				$orientation .= WebsiteGetData::transformeOrientation($value['orientation3']);
			}
			return $orientation;
			
		} else {
				return "?";
		}		
	}

	// input: Rafales 65 km/h
	// return: 21
	static private function getMaxWindClean($htmlValue) {
		if (preg_match('#^Rafales (?<windMax>[0-9]+)#',$htmlValue,$value)>0) {
			$windMax = $value['windMax'];
			return WebsiteGetData::transformeKmhByNoeud($windMax);
		} else {
			return "?";
		}
	}











	// Delete ???
	function getDataURL2($url) 
	{
		MeteoFranceGetData::getDataURL2($url);
		$ch = \curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_USERAGENT, 'LaPoiz Application');
	    $cookies = "foo=bar";
	    curl_setopt($ch, CURLOPT_COOKIE, $cookies);
	    $html = curl_exec($ch);
	    curl_close($ch);
	      
		$dom = new \DomDocument();
		@$dom->loadHTML($html);
		//$dom->save('/tmp/meteoFrancePage.txt');
		return $html; 		
	}

}