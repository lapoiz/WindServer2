<?php
namespace LaPoiz\WindBundle\core\websiteDataManage;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use LaPoiz\WindBundle\Entity\DataWindPrev;
use LaPoiz\WindBundle\Entity\PrevisionDate;
use LaPoiz\WindBundle\Entity\Spot;
use LaPoiz\WindBundle\Entity\WebSite;
use LaPoiz\WindBundle\Entity\Prevision;

class AllPrevGetData extends WebsiteGetData
{
	/**
	 * @param $url
	 * @return Crawler
	 */
	function getDataURL($url)	{
		return null; // don't exist
	}

	/**
	 * Never used
	 * @param $pageHTML: empty
	 * @param $url
	 * @return array|null
	 */
	function analyseData($jsonCrawler,$url)
	{
		$previsionTab = array();
		return $previsionTab;
	}

	function transformData($tableauData) {
		return $tableauData;
	}

	//////*********** SPECIFIQUES FONCTIONS *******************

	/**
	 * @param DataWindPrev $dataWindPrevAllPrev
	 * Delete all data of this dataWindPrev
	 */
	static function deleteOldData(DataWindPrev $dataWindPrevAllPrev,EntityManager $em) {
		foreach ($dataWindPrevAllPrev->getListPrevisionDate() as $previsionDate) {
			$em->remove($previsionDate);
		}
		$em->flush();
	}

	static function calculateWindAllPrev(WebSite $allPrevWebSite, Spot $spot, EntityManager $em) {

		// Find dataWindPrev with allPrevWebsite and spot
		$dataWindPrevAllPrev = $em->getRepository('LaPoizWindBundle:DataWindPrev')->getWithWebsiteAndSpot($allPrevWebSite, $spot);

		// Delete all old data
		AllPrevGetData::deleteOldData($dataWindPrevAllPrev, $em);

		$arrayEveryDay=array();

		// Pour chaque site de prevision remplis le tableau avec ses prévisions (vent, orientation)
		foreach ($spot->getDataWindPrev() as $dataWindPrev) {
			if ($dataWindPrev->getWebsite()->getNom() !== WebsiteGetData::allPrevName ) {
				foreach ($dataWindPrev->getListPrevisionDate() as $previsionDate) {
					AllPrevGetData::getPrevisionValue($arrayEveryDay, $previsionDate,$dataWindPrev->getWebsite()->getNom());
				}
			}
		}
		// $arrayEveryDay['Y-m-d']['H']['wind/orient'][websiteName]
		//$toto = $arrayEveryDay;

		// Calcul la moyenne pour chaque heure (avec coef pour le spot)
		foreach ($arrayEveryDay as $keyDate => $arrayHours) {
			$previsionDate = new PrevisionDate();
			$previsionDate->setDataWindPrev($dataWindPrevAllPrev);
			$previsionDate->setDatePrev(new \DateTime($keyDate));
			$previsionDate->setCreated(new \DateTime("now"));
			$dataWindPrevAllPrev->addListPrevisionDate($previsionDate);


				foreach ($arrayHours as $keyH => $arrayData) {
					// $arrayData['wind/orient'][websiteName]
					$nbWebsite=count($arrayData['wind']);
					if ($nbWebsite>0) {
						$windTotal = 0;
						foreach ($arrayData['wind'] as $keywebsiteName => $windValue) {
							$windTotal += $windValue;
						}
						$orientTotal = 0;
						foreach ($arrayData['orient'] as $keywebsiteName => $orientationValue) {
							$orientTotal += $orientationValue;
						}

						$prevision = new Prevision();
						$prevision->setOrientation($orientTotal / $nbWebsite);// Ne marche pas autour de l'orientation NNE et NNO
						$prevision->setWind($windTotal / $nbWebsite);
						$hour = new \DateTime();
						$hour->setTime($keyH, "00");
						$prevision->setTime($hour);
						$prevision->setPrevisionDate($previsionDate);
						$em->persist($prevision);
					}
				}


			$em->persist($previsionDate);
			$em->persist($dataWindPrevAllPrev);
		}
		$em->flush();

	}

	static function getPrevisionValue(&$arrayEveryDay, PrevisionDate $previsionDate, $webSiteName) {
		if (!array_key_exists($previsionDate->getDatePrev()->format('Y-m-d'),$arrayEveryDay)) {
			$arrayEveryDay[$previsionDate->getDatePrev()->format('Y-m-d')] = AllPrevGetData::createArrayEveryHoure();// tableau de toutes les heures
		}

		$listPrevision = $previsionDate->getListPrevision();

		$prevHour=0;
		$prevOrient=0;
		$prevWind=0;
		foreach ($listPrevision as $prevision) {
			$wind=$prevision->getWind();
			$orientation=WebsiteGetData::transformeOrientationDeg($prevision->getOrientation());
			$hour=intval($prevision->getTime()->format('H'));
			$arrayEveryDay[$previsionDate->getDatePrev()->format('Y-m-d')][$hour]['wind'][$webSiteName]=$wind;
			$arrayEveryDay[$previsionDate->getDatePrev()->format('Y-m-d')][$hour]['orient'][$webSiteName]=$orientation;
			AllPrevGetData::completeArrayPrevision($arrayEveryDay[$previsionDate->getDatePrev()->format('Y-m-d')], $webSiteName, $wind, $orientation, $hour, $prevWind, $prevOrient, $prevHour);

			$prevHour=$hour;
			$prevOrient=$orientation;
			$prevWind=$wind;
		}
	}

	private static function createArrayEveryDay() {
		$result = array();
		$day = new \DateTime("now");
		for ($numDay=0;$numDay<7;$numDay++) {
			$result[$day->format('Y-m-d')]=array();
			$day->modify( '+1 day' );
		}
		return $result;
	}

	private static function createArrayEveryHoure() {
		$result = array();
		for ($i = 0; $i < 24; $i++) {
			$result[$i] = array();// wind et orientation
			$result[$i]['wind'] = array();
			$result[$i]['orient'] = array();
		}
		return $result;
	}

	private static function completeArrayPrevision(&$arrayHours, $webSiteName, $wind, $orientation, $hour, $prevWind, $prevOrient, $prevHour) {
		$nbHourEcart=$hour-$prevHour; // pour ne pas modifier $hour et $prevHour
		if ($nbHourEcart>0) {
			$aW = ($prevWind - $wind) / ($prevHour - $hour);
			$bW = $wind - $hour * $aW;
			$aO = ($prevOrient - $orientation) / ($prevHour - $hour);
			$bO = $orientation - $hour * $aO;
			for ($h = 1; $h <= $nbHourEcart; $h++) {
				$arrayHours[$prevHour + $h]['wind'][$webSiteName] = $aW * ($prevHour + $h) + $bW;
				$arrayHours[$prevHour + $h]['orient'][$webSiteName] = $aO * ($prevHour + $h) + $bO;
			}
		}
	}

}