<?php

namespace LaPoiz\WindBundle\core\note;

use LaPoiz\WindBundle\Command\CreateNoteCommand;
use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;

class NoteTemp {

    /**
     * @param $spot
     * @param $tabNotes : array $tabNotes['Y-m-d'] for next 7 days
     * @param $tabPrevisionDate : tableau des previsionDate pour la même date, mais pour les différents website
     * return: la note vis à vis de la Température
     */
    static function calculNoteTemp($tabPrevisionDate) {

        $noteMF = 0; // note de Meteo France

        $nbNote=0;

        if (count($tabPrevisionDate)>0) {

            foreach ($tabPrevisionDate as $previsionDate) {
                // pour chaque website = $previsionDate (même spot, même jour)

                switch ($previsionDate->getDataWindPrev()->getWebSite()->getNom()) {
                    /*case WebsiteGetData::meteoFranceName :
                        $noteMF=NoteTemp::calculateNoteMeteoFrance($previsionDate);
                        if ($noteMF>=0) {
                            $nbNote++;
                        } else {
                            $noteMF=0;
                        }
                        break;
*/
                    case WebsiteGetData::windFinderName :
                        $noteWF=NoteTemp::calculateNoteWindFinder($previsionDate);
                        if ($noteWF>=0) {
                            $nbNote++;
                        } else {
                            $noteWF=0;
                        }
                        break;
                }
            }
        }
        // calcul la note pour la journée
        if ($nbNote>0) {
            return round(($noteMF+$noteWF)/$nbNote,1); // moyenne sans pondération par site Internet
        } else {
            return -1;
        }
    }


    //////// Functions annexes /////////////////////////////////////////

    // calcul la note des température pour ce previsionDate qui est du site WindFinder
    static function calculateNoteWindFinder($previsionDate) {
        $tempMin=10;
        $tempNav=15;// Température ou ca devient sympa de naviguer
        $nbFoisInfMin=0;
        $nbFoisInfNav=0;
        $nbFoisSupNav=0;

        foreach ($previsionDate->getListPrevision() as $prevision) {

            // si dans la tranche horaire de $prevision->getTime()
            if (NoteWind::isInGoodTime($prevision->getTime())) {
                $temp = $prevision->getTemp();

                if ($temp != null) {
                    if ($temp<$tempMin) {
                        $nbFoisInfMin++;
                    } elseif ($temp<$tempNav) {
                        // si inférieur à tempNav mais sup à tempMin -> c'est bof mais on peut naviguer
                        $nbFoisInfNav++;
                    } else {
                        // sup à tempNav -> COOOOL
                        $nbFoisSupNav++;
                    }
                }
            }
        }

        // On calcule la note
        $note=-1;
        if ($nbFoisSupNav+$nbFoisInfNav+$nbFoisInfMin>0) {
            if ($nbFoisInfMin>0) {
                // si inférieur à tempMin -> on ne navigue pas de la journnée
                $note=0;
            } else {
                $note=($nbFoisSupNav + $nbFoisInfNav*0.5)/($nbFoisSupNav+$nbFoisInfNav+$nbFoisInfMin);
            }
        } // else impossible de calculer la note: pas de données -> -1

        return $note;
    }

    // calcul la note des température pour ce previsionDate qui est du site Meteo France
    static function calculateNoteMeteoFrance($previsionDate) {
        return NoteTemp::calculateNoteMeteoFrance($previsionDate);
    }

    // return true si $time est dans l'horaire de navigation
    static function isInGoodTime($time) {
        $heure=intval($time->format("H"));
        return ($heure>=CreateNoteCommand::HEURE_MATIN && $heure<=CreateNoteCommand::HEURE_SOIR);
    }

}