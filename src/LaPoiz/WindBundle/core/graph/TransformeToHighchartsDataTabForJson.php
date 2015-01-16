<?php
namespace LaPoiz\WindBundle\core\graph;

//use LaPoiz\WindBundle\core\graph\spotJsonObject\JsonWindPrevision;
use LaPoiz\WindBundle\core\graph\spotJsonObject\WindOrientation;

/**
 * @author dapoi
 * Utiliser pour transformer les données du modele en donnée compatible au Bundle LaPoiz\HighchartsBundle
 */
class TransformeToHighchartsDataTabForJson   {
	static private $beginHour= '9'; // begin kite session
	static private $endHour= '19'; // end kite session



    static function createResultJson($spot) {
        $spotJsonObject=new SpotJsonObject($spot->getNom());
        return $spotJsonObject;
    }

    // For same WebSite
	static function transformePrevisionDateList($previsionDateList) {
		$forecastArray=array();
        if ($previsionDateList!=null) {
            foreach ($previsionDateList as $previsionDate) {
                foreach ($previsionDate->getListPrevision() as $prevision) {
                    $date=$previsionDate->getDatePrev();

                    $date->setTime($prevision->getTime()->format('H'),0);
                    //$forecastArray[] = new JsonWindPrevision($date->getTimestamp()*1000,$prevision->getWind()); // not need m-1 because jan=0, feb=1 ... dec=11
                    $forecastElem=array("date"=>($date->getTimestamp()*1000));
                    $forecastElem[$previsionDate->getDataWindPrev()->getWebSite()->getNom()]=$prevision->getWind(); // not need m-1 because jan=0, feb=1 ... dec=11
                    if ($prevision->getOrientation()!='?') {
                        $forecastElem["orientation"]= new WindOrientation($prevision->getOrientation());
                    }
                    $forecastArray[] = $forecastElem;
                };
            }
        }
		return $forecastArray;
	}

    // Pour les marée
    static function transformeMareeDateList($mareeDateList,$doctrine) {

        $forecastArray=array();
        foreach ($mareeDateList as $mareeDate) {
            //$id = $mareeDate['id'];

            //$listPrevision = $doctrine->getRepository('LaPoizWindBundle:PrevisionMaree')->getAllFromMareeDate($mareeDate['id']);
            //foreach ($listPrevision as $previsionMaree) {
            foreach ($mareeDate->getListPrevision() as $previsionMaree) {
                // date
                $date=$mareeDate->getDatePrev();
                $date->setTime($previsionMaree->getTime()->format('H'),$previsionMaree->getTime()->format('i'));
                $forecastElem=array("date"=>($date->getTimestamp()*1000));

                // hauteur
                $forecastElem['maree']=$previsionMaree->getHauteur();

                $forecastArray[] = $forecastElem;
            };
        }
        return $forecastArray;
    }

	static function transformePrevisionDate($previsionDate) {
		$highchartsData=array();
		//$xAxisData=array();
		$currentDay='';
		foreach ($previsionDate->getListPrevision() as $prevision) {
			$date=$previsionDate->getDatePrev();
			$date->setTime($prevision->getTime()->format('H'),0);
			$highchartsData[]=array('date'=>$date->format('Y, m-1, d, H'),'wind'=>array($prevision->getWind()));
			if ($currentDay!=$date->format('d')) {
				//$xAxisData[]=array('year'=>$date->format('Y'),'month'=>$date->format('m-1'),'day'=>$date->format('d'));
				$currentDay=$date->format('d');
			}
		}

		return array("serieName" => $previsionDate->getDataWindPrev()->getWebSite()->getNom(),
					"highchartsData" => $highchartsData
					);
					//,"xAxisData" => $xAxisData);		
	}

	
	// get elem from $dataGraphArray for return array with:
	// [from=>[year,month,day,hour], to=>[year,month,day,hour]]
	static function createElemForXAxisHighchart($dataGraphArray) {
		$xAxisDateTmp=array();
		foreach ($dataGraphArray as $dataGraph) {
			foreach ($dataGraph['dataGraph']['xAxisData'] as $datesData) {
					$xAxisDateTmp[$datesData['year']+'-'+$datesData['month']+'-'+$datesData['day']]=$datesData;
			}
		}

		$xAxisGraphData=array();
		$isFirst=true;
		$previousDateData=null;
		foreach ($xAxisDateTmp as $dateData) {
			if ($isFirst) {
				$isFirst=false;
			} else {
				//$previousDateData['hour']= TransformeToHighchartsData::$beginHour;
				//$dateData['hour']= TransformeToHighchartsData::$endHour;
				$xAxisGraphData[]=array('from'=>$previousDateData, 'to'=>$dateData);
			}
			$previousDateData=$dateData;
		}
		return $xAxisGraphData;
	}
}