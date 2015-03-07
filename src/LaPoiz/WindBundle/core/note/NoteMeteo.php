<?php

namespace LaPoiz\WindBundle\core\note;

use LaPoiz\WindBundle\Command\CreateNoteCommand;
use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;

class NoteMeteo {

    /**
     * @param $spot
     * @param $tabNotes : array $tabNotes['Y-m-d'] for next 7 days
     * @param $tabPrevisionDate : tableau des previsionDate pour la même date, mais pour les différents website
     * return: la note vis à vis de la meteo: precipitation, soleil...
     */
    static function calculNoteMeteo($tabPrevisionDate) {

        $noteWF = 0; // note de WindFinder -> precipitation
        $noteWG = 0; // note de WindGuru
        $noteMF = 0; // note de Meteo France -> soleil

        $nbNote=0;

        if (count($tabPrevisionDate)>0) {

            foreach ($tabPrevisionDate as $previsionDate) {
                // pour chaque website = $previsionDate (même spot, même jour)

                switch ($previsionDate->getDataWindPrev()->getWebSite()->getNom()) {
                    //case WebsiteGetData::windguruName :
                    //    $noteWG=NoteMeteo::calculateNoteWindguru($previsionDate);
                    //    $nbNote++;
                    //    break;
                    case WebsiteGetData::windFinderName :
                        $noteWF=NoteMeteo::calculateNoteWindfinder($previsionDate);
                        $nbNote++;
                        break;
                    case WebsiteGetData::meteoFranceName :
                        $noteMF=NoteMeteo::calculateNoteMeteoFrance($previsionDate);
                        $nbNote++;
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

    // calcul la note de la meteo (precipitation, soleil...) pour ce previsionDate qui est du site WindGuru
    static function calculateNoteWindfinder($previsionDate) {
        $nbFoisPluie=0;
        $nbFoisSansPluie=0;
        $coef=1;

        foreach ($previsionDate->getListPrevision() as $prevision) {

            // si dans la tranche horaire de $prevision->getTime()
            if (NoteWind::isInGoodTime($prevision->getTime())) {
                $precipitation = $prevision->getPrecipitation();
                if ($precipitation>0) {
                    $nbFoisPluie++;
                } else {
                    $nbFoisSansPluie++;
                }
            }
        }
        $note = ($nbFoisSansPluie/($nbFoisPluie+$nbFoisSansPluie));
        if ($precipitation>0) {
            // On applique un coef en fonction de la densité de la pluie
            $precipitationHeure= $precipitation/($nbFoisPluie*3); // x 3 -> car tranche de 3h

            // 3mm de pluie par heure -> pluie modéréé (Wikipedia)
            // http://fr.wikipedia.org/wiki/Pluie
            if ($precipitationHeure<3) {
                $coef=1.25; // on améliore la note
            } elseif ($precipitationHeure>7) {
                $coef=0.75; // on réduit la note
            }
        }

        return $note*$coef;
    }

    // calcul la note de la meteo (precipitation, soleil...) pour ce previsionDate qui est du site Meteo France
    static function calculateNoteMeteoFrance($previsionDate) {

        $isDanger=false;
        foreach ($previsionDate->getListPrevision() as $prevision) {

            // si dans la tranche horaire de $prevision->getTime()
            if (NoteWind::isInGoodTime($prevision->getTime())) {
                $meteo = $prevision->getMeteo();

                if ($meteo == "a-o" || $meteo == "p-o") {
                    // Danger lorsque:
                    // "Averses orageuses" -> "a-o";
                    // "Pluies orageuses" -> "p-o";
                    $isDanger=true;
                }
            }
        }
        return $isDanger?0.5:1;
    }


    // return true si $time est dans l'horaire de navigation
    static function isInGoodTime($time) {
        $heure=intval($time->format("H"));
        return ($heure>=CreateNoteCommand::HEURE_MATIN && $heure<=CreateNoteCommand::HEURE_SOIR);
    }

}