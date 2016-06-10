<?php

namespace LaPoiz\WindBundle\core\nbHoure;

use LaPoiz\WindBundle\Command\CreateNoteCommand;
use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;

class NbHoureWind {

    /**
     * @param $spot
     * @param $tabHoure : array of houre of the day
     * @param $tabPrevisionDate : tableau des previsionDate pour la même date, mais pour les différents website
     */
    static function calculNbHoureNav($spot, &$tabHoure, $tabPrevisionDate) {

        if (count($tabPrevisionDate)>0) {
            // Création d'un tableau des orientations
            $tabRosaceOrientation = array();
            foreach ($spot->getWindOrientation() as $windOrientation) {
                $tabRosaceOrientation[$windOrientation->getOrientation()]=$windOrientation->getState();
            }
            $tabRosaceOrientation['?']='?';
            $tabRosaceOrientation['-1']='?';

            foreach ($tabPrevisionDate as $previsionDate) {
                // pour chaque website = $previsionDate (même spot, même jour)

                switch ($previsionDate->getDataWindPrev()->getWebSite()->getNom()) {
                    case WebsiteGetData::windguruName :
                        //NbHoureWind::calculNbHoureNavWindguru($previsionDate,$tabRosaceOrientation,$tabHoure);
                        // Same than WindFinder -> Aucun interet
                        break;
                    case WebsiteGetData::windguruProName :
                        NbHoureWind::calculNbHoureNavWindguruPro($previsionDate,$tabRosaceOrientation,$tabHoure);
                        break;
                    case WebsiteGetData::windFinderName :
                        NbHoureWind::calculNbHoureNavWindFinder($previsionDate,$tabRosaceOrientation,$tabHoure);
                        break;
                    case WebsiteGetData::meteoFranceName :
                        NbHoureWind::calculNbHoureNavMeteoFrance($previsionDate,$tabRosaceOrientation,$tabHoure);
                        break;
                    case WebsiteGetData::meteoConsultName :
                        NbHoureWind::calculNbHoureNavMeteoConsult($previsionDate,$tabRosaceOrientation,$tabHoure);
                        break;
                    case WebsiteGetData::alloSurfName :
                        NbHoureWind::calculNbHoureNavAlloSurf($previsionDate,$tabRosaceOrientation,$tabHoure);
                        break;

                    case WebsiteGetData::allPrevName :
                        NbHoureWind::calculNbHoureNavAllPrev($previsionDate,$tabRosaceOrientation,$tabHoure);
                        break;
                }
            }
        }
    }


    // calcul le nb Heure de nav
    static private function calculNbHoureNavigation($orientationState, $windPower) {
        if ($orientationState=='KO') {
            return 0;
        } elseif ($orientationState=='OK') {
            if ($windPower>WebsiteGetData::windPowerMinFun) {
                return 1; // max
            } elseif ($windPower>WebsiteGetData::windPowerMin) {
                return 0.5;
            } else {
                return 0;
            }
        } else {
            // orientationState == warn
            if ($windPower>WebsiteGetData::windPowerMinFun) {
                return 0.5; // max
            } else {
                return 0;
            }
        }
    }


    // calcul le nb Heure de nav pour ce previsionDate qui est du site WindGuru
    static private function calculNbHoureNavWindguru($previsionDate,$tabRosaceOrientation,&$tabHoure) {
        // On boucle sur les previsions du spot, du jour, de WindGuru
        NbHoureWind::calculNbHoureNavWebSite($previsionDate,$tabRosaceOrientation,$tabHoure);
    }

    // calcul le nb Heure de nav pour ce previsionDate qui est du site WindGuruPro
    static private function calculNbHoureNavWindguruPro($previsionDate,$tabRosaceOrientation,&$tabHoure) {
        // On boucle sur les previsions du spot, du jour, de WindGuru Pro
        NbHoureWind::calculNbHoureNavWebSite($previsionDate,$tabRosaceOrientation,$tabHoure);
    }

    // calcul le nb Heure de nav pour ce previsionDate qui est du site MeteoFrance
    static private function calculNbHoureNavMeteoFrance($previsionDate,$tabRosaceOrientation,&$tabHoure) {
        // On boucle sur les previsions du spot, du jour, de WindGuru
        NbHoureWind::calculNbHoureNavWebSite($previsionDate,$tabRosaceOrientation,$tabHoure);
    }

    // calcul le nb Heure de nav pour ce previsionDate qui est du site WindFinder
    static private function calculNbHoureNavWindFinder($previsionDate,$tabRosaceOrientation,&$tabHoure) {
        // On boucle sur les previsions du spot, du jour, de WindGuru
        NbHoureWind::calculNbHoureNavWebSite($previsionDate,$tabRosaceOrientation,$tabHoure);
    }

    // calcul le nb Heure de nav pour ce previsionDate qui est du site MeteoConsult
    static private function calculNbHoureNavMeteoConsult($previsionDate,$tabRosaceOrientation,&$tabHoure) {
        // On boucle sur les previsions du spot, du jour, de WindGuru
        NbHoureWind::calculNbHoureNavWebSite($previsionDate,$tabRosaceOrientation,$tabHoure);
    }

    // calcul le nb Heure de nav pour ce previsionDate qui est du site AlloSurf
    static private function calculNbHoureNavAlloSurf($previsionDate,$tabRosaceOrientation,&$tabHoure) {
        // On boucle sur les previsions du spot, du jour, de WindGuru
        NbHoureWind::calculNbHoureNavWebSite($previsionDate,$tabRosaceOrientation,$tabHoure);
    }

    // Calcul le nb Heure de nav pour ce previsionDate de toutes les prevision du spot
    static private function calculNbHoureNavAllPrev($previsionDate,$tabRosaceOrientation,&$tabHoure) {

        // Todo: calcul les prev depuis tous les autres prev
    }

    /**
     * Function principal sur la récupération des données de doctrine dans un tableau utiliser pour calculer
     * @param $previsionDate
     * @param $tabRosaceOrientation
     * @param $tabHoure
     */
    static private function calculNbHoureNavWebSite($previsionDate,$tabRosaceOrientation,&$tabHoure) {
        foreach ($previsionDate->getListPrevision() as $prevision) {
            $houre=intval($prevision->getTime()->format('H'));
            if (NbHoureWind::isInGoodTime($houre)) {
                $tabHoure[$houre][$previsionDate->getDataWindPrev()->getWebSite()->getNom()]=NbHoureWind::generateTabNbHourNav($prevision, $tabRosaceOrientation);
            }
        }
    }

    static private function generateTabNbHourNav($prevision, $tabRosaceOrientation) {
        $orientation=$prevision->getOrientation();
        $windTab=array();
        $windTab['orientation']=$tabRosaceOrientation[$orientation];
        $windTab['wind']=$prevision->getWind();
        $windTab['timeNav']=NbHoureWind::calculNbHoureNavigation($tabRosaceOrientation[$orientation],$prevision->getWind());
        return $windTab;
    }

    // return true si $time est dans l'horaire de navigation
    static public function isInGoodTime($time) {
        return ($time>=CreateNoteCommand::HEURE_MATIN && $time<=CreateNoteCommand::HEURE_SOIR);
    }

}