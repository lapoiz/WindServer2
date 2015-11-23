<?php
namespace LaPoiz\WindBundle\core\websiteDataManage;

use Goutte\Client;
//use LaPoiz\WindBundle\Entity\PrevisionDate;
//use LaPoiz\WindBundle\Entity\Prevision;

class MeteoFranceGetData extends WebsiteGetData
{
	const goodSectionId= 'seven-days';
	const nbJoursPrev=7; // Au dela de 4 jours, pas de prévision de vent...
	const idNameDivDay="detail-day-0";

	function getDataURL($url)	{
		$client = new Client();
		$crawler = $client->request('GET', $url);
		return $crawler;
	}

	/**
	 * @param $pageHTML: crawler de Goutte
	 * @param $url: URL de la page
	 * @return array|null
	 */
	function analyseData($pageHTML,$url) {

		$previsionTab=array();
		if (empty($pageHTML)) {
			return null;
		} else	{
			$section=MeteoFranceGetData::getGoodSection($pageHTML);

			if (empty($section)){
				echo '<br />Element not find is section id="'.MeteoFranceGetData::goodSectionId.'" ... Meteo France change the page ?<br />';
			} else {
				$text=$section->text(); // Only for watch the section value
				/* <div id="detail-day-01" */
				for ($numDay = 1; $numDay <= MeteoFranceGetData::nbJoursPrev; $numDay++) {
					$divDay = $section->filter('#' . MeteoFranceGetData::idNameDivDay . $numDay);
					$previsionTab[$numDay] = array();

					$tr = $divDay->filter('tr');

					// ************* Les horaires *************
					$trHour = $tr->eq(0); // Titre avec les heures

					$thHour=$trHour->filter('th[class!="tomorrow"]');
					$numCol=0;
					foreach ($thHour as $domThHour) {
						$houre = MeteoFranceGetData::getHour($domThHour->nodeValue);
						if ($houre != null) {
							$previsionTab[$numDay][$numCol] = array("houre"=>$houre);
							$numCol++;
						}
					}

					$nbCol=$numCol;
					if ($numDay>1) { // tous sauf le 1er div (avec tomorrow)
						$nbCol--; // la derniere heur correspond à 2h du lendemain
					}

					// ************* Météo *************
					$trMeteo = $tr->eq(1);
					$tdMeteoList=$trMeteo->filterXPath('//td/span');
					$numCol=0;
					foreach ($tdMeteoList as $domTdMeteo) {
						if ($numCol<$nbCol) { // utile pour le premier tr avec jour suivant, non pris en charge
							$previsionTab[$numDay][$numCol]["meteo"]=$domTdMeteo->nodeValue;
						}
						$numCol++;
					}

					// ************* Proba pluie *************
					$trProbaPluie = $tr->eq(3);
					$tdProbaPluie=$trProbaPluie->filterXPath('//td');
					$numCol=0;
					foreach ($tdProbaPluie as $domTdProbaPluie) {
						if ($numCol<$nbCol) { // utile pour le premier tr avec jour suivant, non pris en charge
							$previsionTab[$numDay][$numCol]["probaPluie"]=$domTdProbaPluie->nodeValue;
							$numCol++;
						}
					}

					// ************* Température *************
					$trTemp = $tr->eq(7);
					if ($tr->count()>12) {
						$trTemp = $tr->eq(9);
					}

					$tdTemp=$trTemp->filterXPath('//td/span');
					$numCol=0;
					foreach ($tdTemp as $domTdTemp) {
						if ($numCol<$nbCol) { // utile pour le premier tr avec jour suivant, non pris en charge
							$previsionTab[$numDay][$numCol]["temp"]=$domTdTemp->nodeValue;
							$numCol++;
						}
					}

					// ************* Vent *************

					//$trWind = $tr->eq(11);
                    $trWind = $tr->last();
					$tdWind=$trWind->filterXPath('//td');
					$numCol=0;
					foreach ($tdWind as $domTdWind) {
						if ($numCol<$nbCol) { // utile pour le premier tr avec jour suivant, non pris en charge
							$previsionTab[$numDay][$numCol]["wind"]=$domTdWind->nodeValue;
							$previsionTab[$numDay][$numCol]["orientation"]=$domTdWind->firstChild->getAttribute("class");
							$numCol++;
						}
					}
				}
			}// else
		}// else
		return $previsionTab;
	}

	function transformData($tableauData) {
		$cleanTabData = array();

		$day = new \DateTime('NOW');
		foreach ($tableauData as $lineData) {
			// 1 line = 1 date depuis aujourd'hui

			$cleanElemDay = array();
            $datePrev=$day->format("Y-m-d");

			foreach ($lineData as $key => $lineHoure) {
				// 1 lineHoure = 1 hour
                if (!empty($lineHoure['wind']) && ($lineHoure["houre"]!="2" && $lineHoure["houre"]!="1") || $key==0) {
                    $cleanElemHoure = array();
                    $cleanElemHoure['heure'] = MeteoFranceGetData::getHoureClean($lineHoure["houre"]);
                    $cleanElemHoure['date'] = $datePrev;
                    $cleanElemHoure['wind'] = MeteoFranceGetData::getWindClean($lineHoure['wind']);
                    $cleanElemHoure['maxWind'] = MeteoFranceGetData::getMaxWindClean($lineHoure['wind']);
                    $cleanElemHoure['temp'] = MeteoFranceGetData::getTempClean($lineHoure['temp']);
                    $cleanElemHoure['orientation'] = MeteoFranceGetData::getOrientationClean($lineHoure['orientation']);
                    $cleanElemHoure['meteo'] = MeteoFranceGetData::getMeteoClean($lineHoure['meteo']);
                    $cleanElemHoure['probaPrecipitation'] = MeteoFranceGetData::getProbaPrecipitationClean($lineHoure['probaPluie']);
                    $cleanElemDay[] = $cleanElemHoure;
                }
			}
            $cleanTabData[$datePrev]=$cleanElemDay;
            $day->modify( '+1 day' ); // jour suivant
		}

		return $cleanTabData;
	}


	// retourne l'heure de 14h ou null si ce n'est pas sous le format xxh
	static private function getHour($htmlValue) {
		if (preg_match('#(?<hour>[0-9]+)h#',$htmlValue,$value)>0) {
			return $value["hour"];
		} else {
			return null;
		}
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

	// find the div where table of data is
	static private function getGoodSection($crawler) {
		//return $crawler->filterXPath('//section[@id="'.MeteoFranceGetData::goodSectionId.'"]/.');
		return $crawler->filter('#'.MeteoFranceGetData::goodSectionId);
	}

	// input: 18km/h Rafl. 50Km/h
	// return: 13
	static private function getWindClean($htmlValue) {
		if (preg_match('#^ (?<wind>[0-9]+) km/h[[:alpha:]]*#',$htmlValue,$value)>0) {
			$wind = $value['wind'];
			//return $wind;
			return WebsiteGetData::transformeKmhByNoeud($wind);
		} else {
			return "?";
		}
	}

    // input: 18km/h Rafl. 65Km/h
    // return: 21
    static private function getMaxWindClean($htmlValue) {
        if (preg_match('#[[:alpha:]]*Raf.(?<windMax>[0-9]+)#',$htmlValue,$value)>0) {
            $windMax = $value['windMax'];
            return WebsiteGetData::transformeKmhByNoeud($windMax);
        } else {
            return "?";
        }
    }


	// input: 8 (houre of the begin period)
	// return: 9 (middle of the period)
	// 2 kind of houre:
	// -> 2, 5, 8, 11, 14, 17, 20, 23 -> every 3 hours
	// -> 2, 7, 13, 19 -> every 6 hours
	static private function getHoureClean($houreBegin) {
		if ($houreBegin==7 || $houreBegin==13) {  //||  $houreBegin==19) { 19h -> 20h pour être utilisé
			return $houreBegin+3;
		} elseif ($houreBegin<23) {
			return $houreBegin+1;
		} else {
			return $houreBegin; //$houreBegin==23
		}

	}


    // input: 15Â°C   ou 12°C | 13°C
    // return: 15
    static private function getTempClean($htmlValue) {
        if (preg_match('#[0-9]+#',$htmlValue,$value)>0) {
            return intval($value[0]);
        } else {
            return "?";
        }
    }

    // input: Vent ouest-nord-ouest
    // return: wnw
    static private function getOrientationClean($htmlValue) {
        if (preg_match('#^picVent V_(?<orientation>[NESO]+)#',$htmlValue,$value)>0) {
            $result=strtolower($value['orientation']);
			$result=str_replace('o', 'w', $result);
			return $result;
		} else {
            return "?";
        }
    }

    // input: Pluies orageuses
    // return: p-o
    static private function getMeteoClean($htmlValue) {

        $result = "?";
        switch ($htmlValue) {

            case "Rares averses" :
                $result = "r-a";
                break;
            case "Risque d'orages" :
                $result = "r-o";
                break;
            case "Risque de grêle" :
                $result = "r-g";
                break;
            case "Averses orageuses" :
                $result = "a-o";
                break;
            case "Orages" :
                $result = "o";
                break;

            case "Ensoleillé" :
                $result = "en";
                break;
            case "Éclaircies" :
                $result = "ec";
                break;
            case "Ciel voilé" :
                $result = "c-v";
                break;
            case "Brume" :
                $result = "b";
                break;
            case "Très nuageux" :
                $result = "t-n";
                break;
            case "Pluies éparses" :
                $result = "p-e";
                break;
            case "Averses" :
                $result = "a";
                break;
            case "Pluie" :
                $result = "p";
                break;
            case "Pluies orageuses" :
                $result = "p-o";
                break;
        }
        return $result;
    }


    // input: 50%
    // return: 50
    static private function getProbaPrecipitationClean($htmlValue) {
        if (preg_match('#(?<proba>[0-9]+)%#',$htmlValue,$value)>0) {
            return $value['proba'];
        } else {
            return "?";
        }
    }
}