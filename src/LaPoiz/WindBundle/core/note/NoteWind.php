<?php

namespace LaPoiz\WindBundle\core\note;

use LaPoiz\WindBundle\Command\CreateNoteCommand;
use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;

class NoteWind {

    /**
     * @param $spot
     * @param $tabNotes : array $tabNotes['Y-m-d'] for next 7 days
     * @param $tabPrevisionDate : tableau des previsionDate pour la même date, mais pour les différents website
     * return: la note vis à vis du vent, prenant en compte l'orientation
     */
    static function calculNoteWind($tabPrevisionDate) {

        $noteWF = 0; // note de WindFinder
        $noteWG = 0; // note de WindGuru
        $noteMF = 0; // note de Meteo France

        $nbNote=0;

        if (count($tabPrevisionDate)>0) {

            // Création d'un tableau des orientations
            $tabRosaceOrientation = array();
            foreach ($tabPrevisionDate[0]->getDataWindPrev()->getSpot()->getWindOrientation() as $windOrientation) {
                $tabRosaceOrientation[$windOrientation->getOrientation()]=$windOrientation->getState();
            }
            $tabRosaceOrientation['?']='?';

            foreach ($tabPrevisionDate as $previsionDate) {
                // pour chaque website = $previsionDate (même spot, même jour)

                switch ($previsionDate->getDataWindPrev()->getWebSite()->getNom()) {
                    case WebsiteGetData::windguruName :
                        $noteWG=NoteWind::calculateNoteWindguru($previsionDate,$tabRosaceOrientation);
                        if ($noteWG>=0) {
                            $nbNote++;
                        } else {
                            $noteWG=0;
                        }
                        break;
                    case WebsiteGetData::windFinderName :
                        $noteWF=NoteWind::calculateNoteWindFinder($previsionDate,$tabRosaceOrientation); // même façon de calculer que WindGuru
                        if ($noteWF>=0) {
                            $nbNote++;
                        } else {
                            $noteWF=0;
                        }
                        break;
                    case WebsiteGetData::meteoFranceName :
                        $noteMF=NoteWind::calculateNoteMeteoFrance($previsionDate,$tabRosaceOrientation); // même façon de calculer que WindGuru
                        if ($noteMF>=0) {
                            $nbNote++;
                        } else {
                            $noteMF=0;
                        }
                        break;

                }
            }
        }
        // calcul la note pour la journée
        if ($nbNote>0) {
            return round(($noteWG+$noteWF+$noteMF)/$nbNote,1); // moyenne sans pondération par site Internet
        } else {
            return -1;
        }
    }






    //////// Functions annexes /////////////////////////////////////////

    // calcul la note du vent pour ce previsionDate qui est du site WindGuru ou WindFinder
    static function calculateNoteWindguru($previsionDate,$tabRosaceOrientation) {
        $nbInf12Nds = 0;
        $nbSupr12Nds = 0;
        $nbSupr15Nds = 0;
        $nbOrientationKO=0;

        foreach ($previsionDate->getListPrevision() as $prevision) {

            // si dans la tranche horaire de $prevision->getTime()
            if (NoteWind::isInGoodTime($prevision->getTime())) {
                $wind = $prevision->getWind();
                $orientation=NoteWind::getRosaceName($prevision->getOrientation());
                $stateOrientation=$tabRosaceOrientation[$orientation];

                if ($wind < 12 ) {
                    $nbInf12Nds++;
                } else { //($wind >= 12 )
                    if ($wind > 15 ) {
                        if ($stateOrientation=='OK' || $stateOrientation=='?') {
                            $nbSupr15Nds++;
                        } elseif ($stateOrientation=='warn') {
                            $nbSupr15Nds=$nbSupr15Nds+0.5;// compté 1/2 car mauvaise orientation
                            //$nbInf12Nds=$nbInf12Nds+0.5;
                        } else {
                            $nbOrientationKO++;
                        }
                        // if ($wind > 40 ) ??????
                    } else {
                        if ($stateOrientation=='OK' || $stateOrientation=='?') {
                            $nbSupr12Nds++;
                        } else {
                            // si Warn -> KO car pas assez de vent pour une orientation moyenne
                            $nbOrientationKO++;
                        }
                    }
                }
            }
        }
        if ($nbInf12Nds+$nbSupr12Nds+$nbSupr15Nds+$nbOrientationKO>0) { // on a des chiffres
            return ($nbSupr15Nds+$nbSupr12Nds*0.5)/($nbInf12Nds+$nbSupr12Nds+$nbSupr15Nds+$nbOrientationKO);
        } else {
            return -1;
        }
    }

    // calcul la note du vent pour ce previsionDate qui est du site Meteo France
    static function calculateNoteMeteoFrance($previsionDate,$tabRosaceOrientation) {
        $note=NoteWind::calculateNoteWindguru($previsionDate,$tabRosaceOrientation);
        if ($note<0 && $note<0.7) {
            // MeteoFrance est toujours pessimiste niveau vent
            $note+=0.2; // On ajoute 0.2
        }
        return $note;
    }

    // calcul la note du vent pour ce previsionDate qui est du site WindFinder
    static function calculateNoteWindFinder($previsionDate,$tabRosaceOrientation) {
        // identique à WindGuru
        return NoteWind::calculateNoteWindguru($previsionDate,$tabRosaceOrientation);
    }

    // return true si $time est dans l'horaire de navigation
    static function isInGoodTime($time) {
        $heure=intval($time->format("H"));
        return ($heure>=CreateNoteCommand::HEURE_MATIN && $heure<=CreateNoteCommand::HEURE_SOIR);
    }

    /**
     * @param $bdWindOrientationName: orientation au format de la Base de Données: wnw
     * @return orientation au format Rosace: west-nord-west
     */
    static function getRosaceName($bdWindOrientationName) {
        $orientationRosaceName='?';
        switch ($bdWindOrientationName) {
            case 'n':
                $orientationRosaceName = 'nord';
                break;
            case 'nne':
                $orientationRosaceName = 'nord-nord-est';
                break;
            case 'ne':
                $orientationRosaceName = 'nord-est';
                break;
            case 'ene':
                $orientationRosaceName = 'est-nord-est';
                break;
            case 'e':
                $orientationRosaceName = 'est';
                break;
            case 'ese':
                $orientationRosaceName = 'est-sud-est';
                break;
            case 'se':
                $orientationRosaceName = 'sud-est';
                break;
            case 'sse':
                $orientationRosaceName = 'sud-sud-est';
                break;
            case 's':
                $orientationRosaceName = 'sud';
                break;
            case 'ssw':
                $orientationRosaceName = 'sud-sud-west';
                break;
            case 'sw':
                $orientationRosaceName = 'sud-west';
                break;
            case 'wsw':
                $orientationRosaceName = 'west-sud-west';
                break;
            case 'w':
                $orientationRosaceName = 'west';
                break;
            case 'wnw':
                $orientationRosaceName = 'west-nord-west';
                break;
            case 'nw':
                $orientationRosaceName = 'nord-west';
                break;
            case 'nnw':
                $orientationRosaceName = 'nord-nord-west';
                break;
        }
        return $orientationRosaceName;
    }

}