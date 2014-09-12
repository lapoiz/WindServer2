<?php
namespace LaPoiz\WindBundle\core\websiteDataManage;

use LaPoiz\WindBundle\Entity\PrevisionDate;
use LaPoiz\WindBundle\Entity\Prevision;

class MeteoFranceGetData extends WebsiteGetData
{
	const goodUlClass= 'clearBoth';
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
		/*
			html: 
			<ul class="clearBoth">
				<li class="jour  jourNoJs">
					<dl>
						<dt>Aujourd'hui</dt>
						<dd class=.... général
						<dd ...
						<dd class="detail">
							<div class="bloc_details">
								<ul class="echeances total2">
									<li>
										<dl class="">
											<dt>SoirÃ©e</dt>
											<dd><img alt="Nuit claire" src="meteo/pictos/web/SITE/80/0_b.gif" title="Nuit claire" /></dd>
											<dd class="minmax">17Â°C</dd>
											<dd class="ressent">(Ressentie <strong>17Â°C</strong>)</dd>
											<dd class="vents">Vent 
												<span class="picVents dd_ONO" title="Vent ouest-nord-ouest">Vent ouest-nord-ouest</span>
												<strong>20 km/h</strong>
											</dd>
											<dd class="vents">Rafales <strong>-</strong></dd>
										</dl>
									</li>
									<li>
										<dl class="last">
											<dt>Nuit</dt>
											<dd><img alt="Nuit claire" src="meteo/pictos/web/SITE/80/0_b.gif" title="Nuit claire" /></dd>
											<dd class="minmax">15Â°C</dd>
											<dd class="ressent">(Ressentie <strong>15Â°C</strong>)</dd>
											<dd class="vents">Vent 
											<span class="picVents dd_NO" title="Vent nord-ouest">Vent nord-ouest</span>
											<strong>5 km/h</strong>
											</dd>
											<dd class="vents">Rafales <strong>-</strong></dd>
										</dl>
									</li>
								</ul><!-- .echeances -->
							</div>
							<!-- .bloc_details -->
						</dd>
					</dl>
				</li>

				<li class="jour  jourNoJs">
					<dl>
						<dt>Lundi 16</dt>
						...
		*/
		$dom = new \DOMDocument();
		@$dom->loadHTML($pageHTML);
		//$dom->save('../web/tmp/meteoFrancePage.html');
		
		$tableauData = array();	
		if (empty($dom)) {
			return null;
		} else	{
			$ul=MeteoFranceGetData::getGoodUl($dom);	
			if (empty($ul)){
				echo '<br />Element not find is ul class="'.MeteoFranceGetData::goodUlClass.'" ... correct ?<br /><';
			} else {
				$days = $ul->childNodes; // get all li

				foreach ($days as $day){
					
					// for each day (=li)
					$dayDL = $day->firstChild; // only one per <li>

					$date= MeteoFranceGetData::getDate($dayDL);

					$divDetail= MeteoFranceGetData::getDivDetail($dayDL);

					$listDlDetail = $divDetail->getElementsByTagName('dl');

					foreach ($listDlDetail as $detailDl){
						$previsionTab = array();
						$time = $detailDl->getElementsByTagName('dt')->item(0)->nodeValue;
						$temp='';
						$wind='';
						$windMax='';
						$orientation='';

						$listDdDetail = $detailDl->getElementsByTagName('dd');
						foreach ($listDdDetail as $detailDd){
							$detailDdClass = $detailDd->getAttribute('class');
							if ($detailDdClass==MeteoFranceGetData::tempClass) {
								$temp = $detailDd->nodeValue;
							} elseif ($detailDdClass==MeteoFranceGetData::windClass) {
								// vent ou rafale
								$spanVent=$detailDd->getElementsByTagName('span');
								if ($spanVent->length>0) {
									// vent
									$orientation = $spanVent->item(0)->nodeValue;
									$wind = $detailDd->getElementsByTagName('strong')->item(0)->nodeValue;
								} else {
									// rafale
									$windMax=$detailDd->getElementsByTagName('strong')->item(0)->nodeValue;
								}
							}
						}
						$previsionTab['date'] = $date;
						$previsionTab['time'] = $time;
						$previsionTab['temp'] = $temp;
						$previsionTab['wind'] = $wind;
						$previsionTab['windMax'] = $windMax;
						$previsionTab['orientation'] = $orientation;
						$tableauData[]=$previsionTab;
					}
				}

			}
		}
		return $tableauData;
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
	static private function getGoodUl($dom) {
		$uls = $dom->getElementsByTagName('ul');
		$ulFind=null;
		foreach ($uls as $ul) {
			if ($ul -> getAttribute('class')==MeteoFranceGetData::goodUlClass) {
				$ulFind=$ul;
			}
		}
		return $ulFind;
	}

	// find the value of the date in the dl of the day
	static private function getDate($dayDL) {
		return $dayDL->getElementsByTagName('dt')->item(0)->nodeValue;
	}

	// find the div of the detail in the dl day
	static private function getDivDetail($dayDL) {
		$divs = $dayDL->getElementsByTagName('div');
		$divFind=null;
		foreach ($divs as $div) {
			if ($div -> getAttribute('class')==MeteoFranceGetData::detailDivClass) {
				$divFind=$div;
			}
		}
		return $divFind;
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

	// input: 15Â°C
	// return: 15
	static private function getTempClean($htmlValue) {
		if (preg_match('#[0-9]+#',$htmlValue,$value)>0) {
			return $value[0];
		} else {
			return "?";
		}
	}

	// input: 30 km/h 
	// return: 13
	static private function getWindClean($htmlValue) {
		if (preg_match('#[0-9]+#',$htmlValue,$value)>0) {
			$wind = $value[0];
			return WebsiteGetData::transformeKmhByNoeud($htmlValue);
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

	// input: 55 km/h 
	// return: 21
	static private function getMaxWindClean($htmlValue) {
		if (preg_match('#[0-9]+#',$htmlValue,$value)>0) {
			$wind = $value[0];
			return WebsiteGetData::transformeKmhByNoeud($htmlValue);
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