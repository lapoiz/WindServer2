<?php
namespace LaPoiz\WindBundle\core\websiteDataManage;

use Goutte\Client;
use LaPoiz\WindBundle\Entity\PrevisionDate;
use LaPoiz\WindBundle\Entity\Prevision;
use Symfony\Component\DomCrawler\Crawler;


class WebsiteGetData
{
	const windguruName="Windguru";
    const windguruProName="WindguruPro";
	const windFinderName="WindFinder";
	const meteoFranceName="MeteoFrance";
	const meteoConsultName="MeteoConsult";
	const alloSurfName="AlloSurf";
	// Pour ajout nouveau site, ajouter également dans : LaPoiz\WindBundle\core\websiteDataManage/WebsiteManage.php line 42
	// + créer un loading Fixtures

	const windPowerMin=12;
	const windPowerMinFun=15;

	static function getListWebsiteAvailable() {
		return array(
			WebsiteGetData::meteoFranceName,
			WebsiteGetData::windguruName,
            WebsiteGetData::windguruProName,
			WebsiteGetData::windFinderName,
			WebsiteGetData::meteoConsultName,
			WebsiteGetData::alloSurfName,
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
            case WebsiteGetData::windguruProName:
                return new WindguruProGetData();
                break;
			case WebsiteGetData::windFinderName:
				return new WindFinderGetData();
				break;
			case WebsiteGetData::meteoConsultName:
				return new MeteoConsultGetData();
				break;
			case WebsiteGetData::alloSurfName:
				return new AlloSurfGetData();
				break;
		}
		return $result;
	}

    function getDataURL($url)	{
        $client = new Client();
        $crawler = $client->request('GET', $url);
        return $crawler;
    }

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


    // return true if it's possible to parse the data from $dataWindPrev->URL, false else
    function isdataWindPrevOK($dataWindPrev)
    {
        $url=$dataWindPrev->getUrl();
        $ok = false;

        try {
            $result=$this->getDataURL($url);
            $result=$this->analyseData($result,$url);
            $this->transformData($result);
            $ok = true;
        } 	catch (\Exception $e) {
            $ok = false;
        }

        return $ok;
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
			if ($date !== 'update') {
				$prevDate = new PrevisionDate();
				$prevDate->setCreated($now);

				$prevDate->setDatePrev(new \DateTime($date));

				$windCalculate = array("max" => 0, "min" => 0, "cumul" => 0, "nbPrev" => 0);

				foreach ($lineWindData as $dataPrev) {
					$prev = new Prevision();
					$prev->setOrientation($dataPrev["orientation"]);
					$prev->setWind($dataPrev["wind"]);
					$hour = new \DateTime();
					if (isset($dataPrev["heure"]) && !empty($dataPrev["heure"]) && strlen($dataPrev["heure"]) > 0) {
						$hour->setTime($dataPrev["heure"], "00");
					} else {
						$hour->setTime("01", "00");
					}
					$prev->setTime($hour);

					if (isset($dataPrev["meteo"])) {
						$prev->setMeteo($dataPrev["meteo"]);
					}
					if (isset($dataPrev["precipitation"])) {
						$prev->setPrecipitation($dataPrev["precipitation"]);
					}
					if (isset($dataPrev["temp"])) {
						$prev->setTemp($dataPrev["temp"]);
					}

					WindFinderGetData::calculateWind($windCalculate, $prev);

					$prev->setPrevisionDate($prevDate);
					$prevDate->addListPrevision($prev);
					$entityManager->persist($prevDate);
					$entityManager->persist($prev);
					//$entityManager->flush(); // car sinon les éléments ne sont pas obligatoirement enregistrer dans l'ordre des heures
				}

				//TODO: calculate average etc...
				$prevDate->setWindAverage(0);
				if ($windCalculate["nbPrev"] > 0)
					$prevDate->setWindAverage($windCalculate["max"] / $windCalculate["nbPrev"]);
				$prevDate->setWindMax($windCalculate["max"]);
				$prevDate->setWindMin($windCalculate["min"]);
				$prevDate->setWindGauss(0);
				$prevDate->setWindMiddle(0);

				$prevDate->setDataWindPrev($dataWindPrev);
				$dataWindPrev->addListPrevisionDate($prevDate);

				$entityManager->persist($prevDate);
				$entityManager->flush();
				$result[] = $prevDate;
			} else { // last update
				if (isset($lineWindData[0]) && isset($lineWindData[0][0])) {
					try {
						$dataWindPrev->setLastUpdate(new \DateTime($lineWindData[0][0]));
					} catch (\Exception $e) {
						// DO nothing
					}
				}
			}
		}
		//$entityManager->persist($dataWindPrev);
		//$entityManager->flush();

		return $result;
	}

	/**
	 * @param $windKmh : vent en Km/h
	 * @return float : vitesse en knds (noeuds)
	 */
	static function transformeKmhByNoeud($windKmh) {
		return round($windKmh/1.852,0);
	}

	/*
	 * Determine comment afficher le resultat en fonction de l'�tape des test
	 */
	static function typeDisplay($step) {
		$result="text";
		switch ($step) {
			case 10: $result="text";break; // display error texte
			case 0: $result="text";break;
			case 1: $result="code";break;
			case 2: $result="arrayOfArray";break;
			case 3: $result="arrayOfArrayOfArray";break;
			case 4: $result="prevDate";break;
		}
		return $result;
	}


    function getNodeValue(Crawler $crawler, $filter) {
        if (!is_null($crawler->filter($filter)->getNode(0))) {
            return trim($crawler->filter($filter)->getNode(0)->nodeValue);
        } else {
            return null;
        }
    }

	/**
	 * @param $resulGetData: resultat de la fonction getDataURL
	 * @return ce qu'on affiche
	 */
	static function displayGetData($resultGetDataURL) {
		return $resultGetDataURL; // par defaut retourn un element lisible
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

    static function transformeOrientationDeg($orientationNom) {
        $result=-1;

		switch ($orientationNom) {
			case 'n':
				$result=0;
				break;
            case 'nne':
				$result=22.5;
				break;
            case 'ne':
				$result=45;
				break;
            case 'ene':
				$result=67.5;
				break;
            case 'e':
				$result=90;
				break;
            case 'ese':
				$result=112.5;
				break;
            case 'se':
				$result=135;
				break;
            case 'sse':
				$result=157.5;
				break;
            case 's':
				$result=180;
				break;
            case 'ssw':
				$result=202.5;
				break;
            case 'sw':
				$result=225;
				break;
            case 'wsw':
				$result=247.5;
				break;
            case 'w':
				$result=270;
				break;
            case 'wnw':
				$result=292.5;
				break;
            case 'nw':
				$result=315;
				break;
            case 'nnw':
				$result=337.5;
				break;
        }

        return $result;
    }

    static function transformeOrientationNomLongDeg($orientationNom) {
        $result=-1;

        switch ($orientationNom) {
            case 'nord':
                $result=0;
                break;
            case 'nord-nord-est':
                $result=22.5;
                break;
            case 'nord-est':
                $result=45;
                break;
            case 'est-nord-est':
                $result=67.5;
                break;
            case 'est':
                $result=90;
                break;
            case 'est-sud-est':
                $result=112.5;
                break;
            case 'sud-est':
                $result=135;
                break;
            case 'sud-sud-est':
                $result=157.5;
                break;
            case 'sud':
                $result=180;
                break;
            case 'sud-sud-west':
                $result=202.5;
                break;
            case 'sud-west':
                $result=225;
                break;
            case 'west-sud-west':
                $result=247.5;
                break;
            case 'west':
                $result=270;
                break;
            case 'west-nord-west':
                $result=292.5;
                break;
            case 'nord-west':
                $result=315;
                break;
            case 'nord-nord-west':
                $result=337.5;
                break;
        }
        return $result;
    }
	static function transformeOrientationDegToNom($orientationDeg) {
		$result='';

		if ($orientationDeg < 11.5) {
			$result='n';
		} elseif ($orientationDeg < 33.75) {
			$result='nne';
		} elseif ($orientationDeg < 56.25) {
			$result='ne';
		} elseif ($orientationDeg < 78.75) {
			$result='ene';
		} elseif ($orientationDeg < 101.25) {
			$result='e';
		} elseif ($orientationDeg < 123.75) {
			$result='ese';
		} elseif ($orientationDeg < 146.25) {
			$result='se';
		} elseif ($orientationDeg < 168.75) {
			$result='sse';
		} elseif ($orientationDeg < 191.25) {
			$result='s';
		} elseif ($orientationDeg < 213.75) {
			$result='ssw';
		} elseif ($orientationDeg < 236.25) {
			$result='sw';
		} elseif ($orientationDeg < 258.75) {
			$result='wsw';
		} elseif ($orientationDeg < 281.25) {
			$result='w';
		} elseif ($orientationDeg < 303.75) {
			$result='wnw';
		} elseif ($orientationDeg < 326.25) {
			$result='nw';
		} elseif ($orientationDeg < 348.75) {
			$result='nnw';
		} else {
			$result='n';
		}

		return $result;
	}
}