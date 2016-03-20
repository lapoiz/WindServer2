<?php
namespace LaPoiz\WindBundle\core\websiteDataManage;

use LaPoiz\WindBundle\Entity\PrevisionDate;
use LaPoiz\WindBundle\Entity\Prevision;
use Symfony\Component\DomCrawler\Crawler;

class WindFinderGetData extends WebsiteGetData
{
	const idLastUpdate = '#last-update';            // <span id="last-update">17:38</span>
    const goodSectionClass = '.tab-content';         // <section class="tab-content">
    const dataDayDivClass = '.forecast-day';        // <div class="weathertable forecast-day forecast forecast-day-8">
    const dataDateDivClass = '.weathertable__header';// <div class="weathertable__header">Sunday, Dec 06</div>
    const dataHoureDivClass = '.weathertable__row'; // <div class="weathertable__row row-stripe">
    const dataHoureClass = '.data-time';            // <div class="data-time weathertable__cell">
    const dataWindOrientationClass = '.units-wd-dir';// <span class="data-direction-unit units-wd units-wd-dir data--minor weathertable__cell" style="display: none;">
    const dataWindClass = '.units-ws';              // <span class="units-ws">14</span>
    const dataPrecipitationClass = '.data-rain';    //<div class="data-rain data--minor weathertable__cell">
    const dataTempClass = '.units-at';              // <span class="units-at">11</span>
    const dataWaveClass = '.units-wh';              // <span class="units-wh">1.2</span>
    const dataLastUpdateId= '#last-update';      // <span id="last-update">17:42</span>

	/**
	 * @param $pageHTML: crawler de Goutte
	 * @param $url: URL de la page
	 * @return array|null
	 */
	function analyseData($pageHTML,$url)	{

    /*    $fp = fopen('windFinder_HTML.html', 'w'); // save on web directory
        fwrite($fp, $pageHTML->html());
        fclose($fp);
    */
		$previsionTab = array();
		if (empty($pageHTML)) {
			return null;
		} else {
            $section=$pageHTML->filter(WindFinderGetData::goodSectionClass);
            if (empty($section)){
                echo '<br />Element not find is section id="'.WindFinderGetData::goodSectionClass.'" ... Windfinder change the page ?<br />';
            } else {
                // Dans la section -> on récupére tous les div de data
                foreach ($section->filter(WindFinderGetData::dataDayDivClass) as $divDayDomElem)  {
                    $divDayData = new Crawler($divDayDomElem);
                    $date=$divDayData->filter(WindFinderGetData::dataDateDivClass)->getNode(0)->nodeValue;
                    $previsionDateTab = array();
                    foreach ($divDayData->filter(WindFinderGetData::dataHoureDivClass) as $divHoureDomElem)  {
                        $divHoureData=new Crawler($divHoureDomElem);
                        $previsionHoureTab = array();
                        $previsionHoureTab['houre']=$this->getNodeValue($divHoureData,WindFinderGetData::dataHoureClass);
                        $previsionHoureTab['orientation']=$this->getNodeValue($divHoureData,WindFinderGetData::dataWindOrientationClass);
                        $previsionHoureTab['wind']=$this->getNodeValue($divHoureData,WindFinderGetData::dataWindClass);
                        $previsionHoureTab['windMax']=$this->getNodeValue($divHoureData,WindFinderGetData::dataWindClass);
                        $previsionHoureTab['precipitation']=$this->getNodeValue($divHoureData,WindFinderGetData::dataPrecipitationClass);
                        $previsionHoureTab['temp']=$this->getNodeValue($divHoureData,WindFinderGetData::dataTempClass);
                        $previsionHoureTab['wave']=$this->getNodeValue($divHoureData,WindFinderGetData::dataWaveClass);
                        $previsionDateTab[]=$previsionHoureTab;
                    }
                    $previsionTab[$date]=$previsionDateTab;
                }
                $previsionTab['lastUpdate']=array(array($this->getNodeValue($section,WindFinderGetData::dataLastUpdateId)));
            }
            return $previsionTab;
		}
	}


    function transformData($tableauData) {
        $cleanTabData = array();

        $day = new \DateTime('NOW');
        foreach ($tableauData as $date => $lineDayData) {
            // 1 line = 1 date
            if ($date!='lastUpdate') {
                $cleanElemDay = array();

                foreach ($lineDayData as $key => $lineHoure) {
                    // 1 lineHoure = 1 hour
                    $cleanElemHoure = array();

                    $cleanElemHoure['heure'] = WindFinderGetData::getHoureClean($lineHoure["houre"]);
                    $cleanElemHoure['wind'] = $lineHoure['wind'];
                    $cleanElemHoure['maxWind'] = $lineHoure['windMax'];
                    $cleanElemHoure['temp'] = $lineHoure['temp'];
                    $cleanElemHoure['orientation'] = WindFinderGetData::getOrientationClean($lineHoure['orientation']);

                    $cleanElemDay[] = $cleanElemHoure;
                }
                $datePrev = WindFinderGetData::getDateClean($date);
                $cleanTabData[$datePrev] = $cleanElemDay;
            } else { // lastUpdate
                $cleanTabData['update'] = array(array(WindFinderGetData::getLastUpdateClean($lineDayData[0][0])));
            }
        }

        return $cleanTabData;
    }

    // $htmlData: Sunday, Dec 06
    // return: 2015-12-06
    static private function getDateClean($htmlData) {
        //$this->get('logger')->err('htmlDate:'+$htmlData);
        $htmlData = trim($htmlData);
        preg_match('#([0-9]{2})$#',$htmlData,$data);

        if ($data[1]>=date('d'))
            return date("Y-m-").$data[1];
        elseif (date('m')<=11)
            return date("Y-").(date('m')+1).'-'.$data[1];
        else
            return (date("Y")+1).'-01-'.$data[1];
    }
    // 08h
    static private function getHoureClean($htmlData) {
        preg_match('#([0-9]{2})h#',$htmlData,$data);
        return $data[1];
    }
    // $htmlData: SSW
    // return: ssw
    static private function getOrientationClean($htmlData) {
        return strtolower($htmlData);
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
     * @param $lastUpdateHTML du type "17:42"
     * @return 2015-12-13 17:42:00
     */
    private function getLastUpdateClean($lastUpdateHTML) {
        // si $lastUpdateHTML plus tot que NOW -> 1 jour de moins
        $now = new \DateTime('NOW');
        $lastUpdateDT = new \DateTime('NOW');

        preg_match('#([0-9]+):([0-9]+)#',$lastUpdateHTML,$data);
        $lastUpdateDT->setTime($data[1],$data[2]);

        if ($lastUpdateDT > $now) {
            // enlever 1 jour
            $lastUpdateDT->modify('-1 day');
        }
        return $lastUpdateDT->format('Y-m-d H:i:s');
    }

    /**
     * @param $resulGetData: resultat de la fonction getDataURL, ici Crawler
     * @return ce qu'on affiche
     */
    static function displayGetData($resultGetDataURL) {
        return $resultGetDataURL->text();
    }


    /*
        function transformData($htmlTabData) {
            $cleanTabData = array();
            $whichColumn = WindFinderGetData::getWichColumTab($htmlTabData);

            $today = WindFinderGetData::getTodayFromHTML($htmlTabData);
            $nbLineTabPerLineWeb = $whichColumn["nbLineTabPerLineWeb"];
            $timeCol = $whichColumn["time"];

            // Page WindFinder construit sur 4 blocks de 22 lignes. Chaques blocs représentant 2 jours
            for ($numLine=0;$numLine<(WindFinderGetData::nbLine);$numLine++) {

                $nbCol=count($htmlTabData[$numLine*$nbLineTabPerLineWeb+$timeCol])-1;
                $prevHoure=WindFinderGetData::getTimePrevFromHTML($htmlTabData[$numLine*$nbLineTabPerLineWeb+$timeCol][1]);
                $datePrev = WindFinderGetData::getDatePrevFromHTML($htmlTabData[$numLine*$nbLineTabPerLineWeb][1]);
                // Boucle sur toutes les colonnes du block (2 jours)
                for ($numCol=0;$numCol<$nbCol;$numCol++) {
                    $timePrev = WindFinderGetData::getTimePrevFromHTML($htmlTabData[$numLine*$nbLineTabPerLineWeb+$timeCol][$numCol+1]);
                    //echo '<br/>$timePrev:'.$timePrev.'  $prevHoure:'.$prevHoure.'  $datePrev:'.$datePrev.'  $numLine'.$numLine.'   $numCol:'.$numCol;
                    if ($timePrev<$prevHoure) {
                        // new day
                        $datePrev = WindFinderGetData::getDatePrevFromHTML($htmlTabData[$numLine*$nbLineTabPerLineWeb][2]);
                    }
                    // Récupere toutes les infos du block
                    $lineData = WindFinderGetData::getWindFinderHtmlData($htmlTabData,$whichColumn,$numLine,$numCol,$today,$datePrev,$timePrev);
                    //$prevHoure=$lineData['timePrev'];
                    $prevHoure=$lineData['heure'];

                    $cleanTabData[$datePrev][] = $lineData;
                }
            }
            return $cleanTabData;
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
            $colClean['precipitation'] = WindFinderGetData::getPrecipitationPrevFromHTML($htmlTabData[$numLine*$nbLineTabPerLineWeb+$whichColumn["precipitation"]][$numCol]);
            return $colClean;
        }


        static private function getTodayFromHTML($htmlData) {
            return date("Y-m-d");
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

        static private function getPrecipitationPrevFromHTML($htmlData) {
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
         *
        static private function getCompleteDate($date) {
            $today= new \DateTime("now");
            if ($today->format('d') > $date) {
                //nest month
                $today->modify( '+1 month' );
            }
            $result=$today->format('Y-m-').$date;
            return $result;
        }


        /*
         * 3627|3|1281931200|169|'France - Saint-Aubin-sur-Mer'|'16.08. 2010 06'|1|0|2|23|46|49.8768|0.8025
         *
        private function getDateFromHTML($htmlLine) {
            preg_match('#([0-9]{2}).([0-9]{2}).\s([0-9]{4})\s#',$htmlLine[5],$data);
            return $data[3].'-'.$data[2].'-'.$data[1];
        }
    */
}