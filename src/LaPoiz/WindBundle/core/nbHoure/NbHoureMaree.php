<?php

namespace LaPoiz\WindBundle\core\nbHoure;

use LaPoiz\WindBundle\core\maree\MareeTools;

class NbHoureMaree {



    /**
     * @param $spot
     * @param $tabHoure : array $tabHoure[$nbHoure] for the day
     * @param $mareeDate : prévision de marre que l'on va analyser
     * Remplis le $tabHoure avec le temps de navigation possible pour chaque heure=elem du tab
     */
    static function calculNbHoureMaree($spot, $tabHoure, $mareeDate) {

            $listePrevisionMaree=$mareeDate->getListPrevision(); // liste des marées extreme de la journée
            if ($listePrevisionMaree!=null && count($listePrevisionMaree)>=2) {
                // *** Calcul de la formule de la courbe: y = a  sin(wt + Phi) + b ***
                // t=time en seconde, y=hauteur en metre, w: phase 2 pi / T , T: fréquence
                // résolution de l'équation

                // calcul le temps de nav par heure
                MareeTools::calculNbHoureNav($listePrevisionMaree, $spot, $tabHoure);
            }
    }

} 