<?php

namespace LaPoiz\WindBundle\core\nbHoure;


use LaPoiz\WindBundle\Command\CreateNbHoureCommand;
use LaPoiz\WindBundle\core\maree\MareeTools;
use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;

class NbHoureMeteo {

    static function calculateMeteoNav($tabNbHoureMeteo)
    {

        $tabMeteo = array();

        foreach ($tabNbHoureMeteo as $date => $jourTab) {
            $tempMax = -40;
            $tempMin = 40;
            $meteoBest = "i"; // Iceberg
            $meteoWorst = "en";// Ensoleillé

            foreach ($jourTab as $key => $data) {

                    $temp = $data["temp"];
                    $meteo = $data["meteo"];
                    //$precipitation = $data["precipitation"];

                    $tempMax = $temp > $tempMax ? $temp : $tempMax;
                    $tempMin = $temp < $tempMin ? $temp : $tempMin;

                    $meteoBest = NbHoureMeteo::isMeteoBest($meteoBest, $meteo) ? $meteoBest : $meteo;
                    $meteoWorst = NbHoureMeteo::isMeteoWorst($meteoWorst, $meteo) ? $meteoWorst : $meteo;

            }
            $tabMeteo[$date] = array();
            $tabMeteo[$date]["tempMax"] = $tempMax;
            $tabMeteo[$date]["tempMin"] = $tempMin;
            $tabMeteo[$date]["meteoBest"] = $meteoBest;
            $tabMeteo[$date]["meteoWorst"] = $meteoWorst;
        }

        return $tabMeteo;
    }

    /**
     * Return true if $meteo1 is better than meteo2, else return false
     */
    static private function isMeteoBest($meteo1, $meteo2) {
        $valueMeteo1=NbHoureMeteo::valueMeteo($meteo1);
        $valueMeteo2=NbHoureMeteo::valueMeteo($meteo2);
        return $valueMeteo1 < $valueMeteo2;
    }

    static private function isMeteoWorst($meteo1, $meteo2) {
        $valueMeteo1=NbHoureMeteo::valueMeteo($meteo1);
        $valueMeteo2=NbHoureMeteo::valueMeteo($meteo2);
        return $valueMeteo1 > $valueMeteo2;
    }

    /**
     * @param $meteo
     * Return the value of $meteo
     * the good meteo have sup value than bad meteo
     */
    static private function valueMeteo($meteo) {
        return MeteoFranceIcon::getInstance()->getTabMeteo()[$meteo];
    }

}