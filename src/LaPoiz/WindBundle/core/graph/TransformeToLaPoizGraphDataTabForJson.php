<?php
namespace LaPoiz\WindBundle\core\graph;

use LaPoiz\WindBundle\core\graph\spotJsonObject\WindOrientation;

/**
 * @author dapoi
 * Utiliser pour transformer les données du modele en donnée compatible au Bundle LaPoiz\GraphBundle
 */
class TransformeToLaPoizGraphDataTabForJson   {
	static private $beginHour= '9'; // begin kite session
	static private $endHour= '19'; // end kite session



    static function createResultJson($spot) {
		$tabJson=array();
		$tabJson["spot"]=$spot->getNom();
		$tabJson["forecast"]=array();
        return $tabJson;
    }

    // For same WebSite
	static function transformePrevisionDateList($previsionDateList) {
		$forecastArray=array();
        if ($previsionDateList!=null) {
            foreach ($previsionDateList as $previsionDate) {
				$forecastDateArray=array();
				$forecastDateArray['date']=$previsionDate->getDatePrev()->format('d-m-Y');
				$forecastDateArray['previsions']=array();
                foreach ($previsionDate->getListPrevision() as $prevision) {
					$previ=array();
					$previ["heure"]=$prevision->getTime()->format('H');
					$previ["wind"]=$prevision->getWind();

                    if ($prevision->getOrientation()!='?') {
						$previ["orientation"]= new WindOrientation($prevision->getOrientation());
                    }
                    if ($prevision->getMeteo()!=null && $prevision->getMeteo()!='?') {
						$previ["meteo"]= $prevision->getMeteo();
                    }
                    if ($prevision->getPrecipitation()!=null && $prevision->getPrecipitation()!='?') {
						$previ["precipitation"]= $prevision->getPrecipitation();
                    }
					$forecastDateArray['previsions'][]=$previ;
                };
				$forecastArray[]=$forecastDateArray;
            }
        }
		return $forecastArray;
	}





	// NON UTILISE POUR L'INSTANT
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