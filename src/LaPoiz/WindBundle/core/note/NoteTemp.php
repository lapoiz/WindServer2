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
                    case WebsiteGetData::meteoFranceName :
                        $noteMF=NoteTemp::calculateNoteMeteoFrance($previsionDate);
                        $nbNote++;
                        break;
                }
            }
        }
        // calcul la note pour la journée
        if ($nbNote>0) {
            return round($noteMF/$nbNote,1); // moyenne sans pondération par site Internet
        } else {
            return -1;
        }
    }


    //////// Functions annexes /////////////////////////////////////////

    // calcul la note du vent pour ce previsionDate qui est du site Meteo France
    static function calculateNoteMeteoFrance($previsionDate) {

        foreach ($previsionDate->getListPrevision() as $prevision) {
            $note=1;
            // si dans la tranche horaire de $prevision->getTime()
            if (NoteWind::isInGoodTime($prevision->getTime())) {
                $temp = $prevision->getTemp();

                if ($temp != null) {
                    if ($temp<10) {
                        // si inférieur à 10°C -> on ne navigue pas de la journnée
                        $note=0;
                    } elseif ($temp<15) {
                        // si inférieur à 15°C et sup à 10°C -> warning
                        if ($note>0.5) {
                            $note=0.5;
                        }
                    }
                }
            } else {
                $note =-1;
            }
        }
        return $note;
    }


    // return true si $time est dans l'horaire de navigation
    static function isInGoodTime($time) {
        $heure=intval($time->format("H"));
        return ($heure>=CreateNoteCommand::HEURE_MATIN && $heure<=CreateNoteCommand::HEURE_SOIR);
    }

}