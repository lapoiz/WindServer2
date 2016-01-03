<?php
namespace LaPoiz\WindBundle\core\websiteDataManage;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

class MeteoConsultGetData extends WebsiteGetData
{

	const nbJoursPrev=7; // Au dela de 4 jours, pas de prévision de vent...

	const sectionHeureId='tabsRefReglette'; // <div id="tabsRefReglette" style="position:absolute; top:0; left:0;">
	const idClassHeureDiv='tabs_echs';		// <div class="tabs_echs ech24">21h</div>

	const dataSelector='div.DataTab';
	const idClassRowDataTab='div.newRow';
	const idClassWind='.Vitesse';
	const idClassOrientation='.Direction';
	const attrOrientation='unitrose8';
	const idClassTemp='.dataTempe';

	const updateDateSelector= '#conteneur_principal > div > div > div > div > div > div > span';


	/**
	 * @param $pageHTML: crawler de Goutte
	 * @param $url: URL de la page (ex:http://marine.meteoconsult.fr/meteo-marine/meteo-spots-de-glisse/manche/previsions-meteo-saint-aubin-sur-mer-185-0.php)
	 * @return array|null
	 *
	 * 1 url / jour ...
	 * ex: http://marine.meteoconsult.fr/meteo-marine/meteo-spots-de-glisse/manche/previsions-meteo-saint-aubin-sur-mer-185-0.php
	 * today+1 : http://marine.meteoconsult.fr/meteo-marine/meteo-spots-de-glisse/manche/previsions-meteo-saint-aubin-sur-mer-185-1.php
	 * ...
	 */
	function analyseData($pageHTML,$url)
	{
		$previsionTab = array();
		if (empty($pageHTML)) {
			return null;
		} else {
			$previsionTab[] = $this->getPrevisionData($pageHTML);

			for ($numJour = 1; $numJour <= MeteoConsultGetData::nbJoursPrev; $numJour++) {
				$urlDay = str_replace('-0.php', '-'.$numJour.'.php', $url);
				$pageHTML = $this->getDataURL($urlDay);
				$previsionTab[] = $this->getPrevisionData($pageHTML);
			}

			// put the update value
			$previsionTab['lastUpdate']=array(array($this->getUpdateDate($pageHTML)));

			return $previsionTab;
		}
	}

		/**
		 * @param $pageHTML : crawler de goutte
		 * @return tableau contenant pour chaque heure les prévisions de vent, precipitation, T°C ...
		 */
	private function getPrevisionData($pageHTML) {

		// Récupére les heures
		$tabDataDay=$this->getHeureTab($pageHTML);

		$dataCrawler= $pageHTML->filter(MeteoConsultGetData::dataSelector);

		//$toto=$dataCrawler->eq(0)->html(); // Wind
		//$toto1=$dataCrawler->eq(1)->html(); // Sea
		//$toto2=$dataCrawler->eq(2)->html(); // Meteo

		$sectionMeteo=$dataCrawler->eq(2);
		$divMeteoHoure=$sectionMeteo->filter(MeteoConsultGetData::idClassRowDataTab);

		$sectionWind=$dataCrawler->eq(0);

		if (empty($sectionWind)){
			return null;
		} else {
			$divWindsHoure=$sectionWind->filter(MeteoConsultGetData::idClassRowDataTab);
			$numHoure=0;
			$lenghtTabHoure=count($tabDataDay);
			foreach ($tabDataDay as $tabDataHoure) {
				$divAllWinds=$divWindsHoure->eq($numHoure);
				$divAllMeteo = $divMeteoHoure->eq($numHoure);
				$tabDataDay[$numHoure]["wind"]=$divAllWinds->filter(MeteoConsultGetData::idClassWind)->html();
				$tabDataDay[$numHoure]["orientation"]=$divAllWinds->filter(MeteoConsultGetData::idClassOrientation)->attr(MeteoConsultGetData::attrOrientation);
				$tabDataDay[$numHoure]["temp"] = $divAllMeteo->filter(MeteoConsultGetData::idClassTemp)->html();
				$numHoure++;
			}
		}

		return $tabDataDay;
	}

	function transformData($tableauData) {
		$cleanTabData = array();

		$day = new \DateTime('NOW');
		foreach ($tableauData as $keyDate=>$lineData) {
			// 1 line = 1 date depuis aujourd'hui
			if ($keyDate!=="lastUpdate") {
				$cleanElemDay = array();
				$datePrev = $day->format("Y-m-d");

				foreach ($lineData as $key => $lineHoure) {
					// 1 lineHoure = 1 hour

						$cleanElemHoure = array();
						$cleanElemHoure['heure'] = MeteoConsultGetData::getHoureClean($lineHoure["heure"]);
						$cleanElemHoure['date'] = $datePrev;
						$cleanElemHoure['wind'] = $lineHoure['wind'];
						$cleanElemHoure['temp'] = $lineHoure['temp'];
						$cleanElemHoure['orientation'] = MeteoConsultGetData::getOrientationClean($lineHoure['orientation']);
						$cleanElemDay[] = $cleanElemHoure;

				}
				$cleanTabData[$datePrev] = $cleanElemDay;
				$day->modify('+1 day'); // jour suivant
			} else { // lastUpdate
				$cleanTabData["update"] = array(array($this->cleanLastUpdate($lineData[0][0])));
			}
		}

		return $cleanTabData;
	}


	// section où se trouve les heures
	private function getSectionHeure($crawler) {
		return $crawler->filter('#'.MeteoConsultGetData::sectionHeureId);
	}

	/**
	 * @param $pageHTML
	 * @return tableau de tableau ayant comme 1er element l'heure
	 */
	private function getHeureTab($pageHTML) {
		$dataTab=array();
		$sectionHeure = $this->getSectionHeure($pageHTML);
		$divHour=$sectionHeure->filter('.'.MeteoConsultGetData::idClassHeureDiv);

		$endOfDay=false;

		/*$nodeValues = $sectionHeure->filter('.'.MeteoConsultGetData::idClassWindDiv)->each(function (Crawler $node, $i) {
			$houre=$node->text();
				$heureTab=array('heure'=>$houre);
				//$dataTab[$houre]=$heureTab;
				$dataTab[]=$heureTab;

		});*/

		foreach ($divHour as $domDivHour) {
			$houre = $domDivHour->nodeValue;
			if (!$endOfDay) {
				$heureTab=array('heure'=>$houre);
				//$dataTab[$houre]=$heureTab;
				$dataTab[]=$heureTab;
			}
			if ('23h'===$houre) {
				$endOfDay=true;
			}
		}
		return $dataTab;
	}

	static function typeDisplay($step) {
		$result="text";
		switch ($step) {
			case 10: $result="text";break; // display error texte
			case 0: $result="text";break;
			case 1: $result="code";break;
			case 2: $result="arrayOfArrayOfArray";break;
			case 3: $result="arrayOfArrayOfArray";break;
			case 4: $result="prevDate";break;
		}
		return $result;
	}

	/**
	 * @param $resulGetData: resultat de la fonction getDataURL, ici Crawler
	 * @return ce qu'on affiche
	 */
	static function displayGetData($resultGetDataURL) {
		return $resultGetDataURL->text();
	}


		/**
		 * @param $pageHTML : crawler de goutte
		 * @return la date de l'update
		 */
	private function getUpdateDate($pageHTML) {

		try {
			$dateCrawler = $pageHTML->filter(MeteoConsultGetData::updateDateSelector);
			$nbElem=$dateCrawler->count();
			$previsionHTML = $dateCrawler->eq($nbElem-1)->html(); // Prévisions établies à 18h00 | prochaine mise à jour à 21h00
			//$previsionText = $dateCrawler->eq($nbElem-1)->text(); // Prévisions établies à 18h00 | prochaine mise à jour à 21h00
			return $this->getUpdateDateFromHTML($previsionHTML);
		} catch (\Exception $e) {
			return null;
		}
		return null;
	}

	// input: Prévisions établies à 18h00 | prochaine mise à jour à 21h00
	// return: 18h00
	private function getUpdateDateFromHTML($htmlValue) {
		if (preg_match('#(\d+h\d+)#',$htmlValue,$value)>0) {
			return $value[0];
		} else {
			return null;
		}
	}

	// input: 18h
	// return: 18
	static private function getHoureClean($htmlValue) {
		if (preg_match('#[0-9]+#',$htmlValue,$value)>0) {
			return intval($value[0]);
		} else {
			return "?";
		}
	}

	// input: SW
	// return: sw
	static private function getOrientationClean($htmlValue) {
		return strtolower($htmlValue);
	}


	/**
	 * @param $lastUpdateHTML du type: "18h00"
	 * @return: "2016-01-03 18:00:00"
	 */
	private function cleanLastUpdate($lastUpdate)	{
		$now = new \DateTime('NOW');
		return $now->format('Y-m-d').' '.str_replace('h',':',$lastUpdate).':00';
	}

}