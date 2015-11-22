<?php

namespace LaPoiz\WindBundle\core\note;

use LaPoiz\WindBundle\Command\CreateNoteCommand;
use LaPoiz\WindBundle\core\maree\MareeTools;

class NoteMaree {

    /**
     * @param $spot
     * @param $tabNotes : array $tabNotes['Y-m-d'] for next 7 days
     * @param $mareeDate : prévision de marre que l'on va analyser
     * return: la note vis à vis de la marée
     */
    static function calculNoteMaree($spot, $tabNotes, $mareeDate) {

        // vérifie que $mareeDate->getDatePrev() soit dans $tabNotes
        if (NoteMaree::isInTabNotes($tabNotes,$mareeDate->getDatePrev()) && $spot->getMareeRestriction() !=null) {

            $listePrevisionMaree=$mareeDate->getListPrevision();
            if ($listePrevisionMaree!=null && count($listePrevisionMaree)>=2) {
                // *** Calcul de la formule de la courbe: y = a  sin(wt + Phi) + b ***
                // t=time en seconde, y=hauteur en metre, w: phase 2 pi / T , T: fréquence
                // résolution de l'équation

                // *** calcul le temps ou la marée est OK, warn et OK ***
                $timeMareeOK = 0;
                $timeMareeWarn = 0;
                $timeMareeKO = 0;

                $timeMareeOKTab = array();
                $timeMareeWarnTab = array();
                $timeMareeKOTab = array();

                // pour chaque $restriction de  $spot->getMareeRestriction()
                foreach ($spot->getMareeRestriction() as $mareeRestriction) {
                    // calcul l'heure (minute) d'intersection pour calculer le temps dans l'etat
                    list($timeInState, $timeTab)=MareeTools::calculTimeInState($mareeRestriction, $listePrevisionMaree);
                    $mareeState=$mareeRestriction->getState();

                    switch ($mareeState) {
                        case "OK" :
                            $timeMareeOK += $timeInState;
                            $timeMareeOKTab = $timeTab;
                            break;
                        case "KO" :
                            $timeMareeKO += $timeInState;
                            $timeMareeKOTab = $timeTab;
                            break;
                        case "warn" :
                            $timeMareeWarn += $timeInState;
                            $timeMareeWarnTab = $timeTab;
                            break;
                    }
                }
                // calcul de la note vis à vis de du temps de navigation / temps etat ok, warn et KO
                $tabNotes[$mareeDate->getDatePrev()->format('Y-m-d')]["marée"]=NoteMaree::getNote($timeMareeOK, $timeMareeWarn, $timeMareeKO);

                $tabNotes[$mareeDate->getDatePrev()->format('Y-m-d')]["maréeTimeOK"]=$timeMareeOKTab;
                $tabNotes[$mareeDate->getDatePrev()->format('Y-m-d')]["maréeTimeWarn"]=$timeMareeWarnTab;
                $tabNotes[$mareeDate->getDatePrev()->format('Y-m-d')]["maréeTimeKO"]=$timeMareeKOTab;
            }
        }
        return $tabNotes;
    }



    /**
     * @param $tabNotes: array $tabNotes['Y-m-d'] for next 7 days
     * @param $datePrev: check if his date is on $tabNotes
     * return true if is in
     */
    static function isInTabNotes($tabNotes, $datePrev) {
        return $datePrev!=null ? is_array($tabNotes[$datePrev->format('Y-m-d')]) : false;
    }


    /**
     * @param $timeMareeOK
     * @param $timeMareeWarn
     * @param $timeMareeKO
     * Calcul la note en fonction du temps de chaque état
     */
    static function getNote($timeMareeOK, $timeMareeWarn, $timeMareeKO) {
        $totalTime = $timeMareeOK+$timeMareeWarn+$timeMareeKO;
        if ($totalTime>0) {
            return round(($timeMareeOK + 0.5 * $timeMareeWarn) / $totalTime,1);
        } else {
            return -1;
        }
    }

    //////// Functions annexes a enlever /////////////////////////////////////////



    /**
     * @param $previsionMaree: element contenant la date et time
     * return : la valeur en minute standard (des axes des x)

    static function getXTime($previsionMaree) {
        $day=$previsionMaree->getMareeDate()->getDatePrev();
        $day->setTime($previsionMaree->getTime()->format('H'),$previsionMaree->getTime()->format('i'));
        return $day->getTimestamp();
    }


    static function getInter($tabDataSinu, $k, $yInter) {
        $y=doubleval($yInter);
        if ($k % 2 == 0) {
            //$k paire
            $k= ($k == 0 ? 0 : $k/2);
            return (asin(round(($y-$tabDataSinu['b'])/$tabDataSinu['a'],16))-$tabDataSinu['phi'] + $k*2*pi())/$tabDataSinu['w'];
        } else {
            //$k impaire -> -pi
            $k=$k-1;
            $k= ($k == 0 ? 0 : $k/2);
            return (pi()-asin(round(($y-$tabDataSinu['b'])/$tabDataSinu['a'],16))-$tabDataSinu['phi'] + $k*2*pi())/$tabDataSinu['w'];
        }

    }

    static function buildSinusoidal($listePrevisionMaree, $tBegin) {

        // Récupére les 2 premiers points
        $previsionMaree1 = $listePrevisionMaree->first();
        $previsionMaree2 = $listePrevisionMaree->next();
        $t1=NoteMaree::getXTime($previsionMaree1);
        $y1=$previsionMaree1->getHauteur();
        $t2=NoteMaree::getXTime($previsionMaree2);
        $y2=$previsionMaree2->getHauteur();

        if ($y1>$y2) {
            $yMax=$y1;
            $tHMax=$t1;
            $yMin= $y2;
            $tHMin=$t2;
        } else {
            $yMax=$y2;
            $tHMax=$t2;
            $yMin= $y1;
            $tHMin=$t1;
        }
        // y = a  sin(wt + Phi) + b

        // w=2 pi / T
        $w= pi() / ($t2-$t1); // $t2 - $t1 : demi periode -> disparition du 2
        $w=$w>=0?$w:-$w; // $w >0

        $a=($yMax-$yMin)/2;
        $b=($yMax+$yMin)/2;

        $phi = asin(round(($y1-$b)/$a,10))-$w*$t1;

        $periode = 2*pi()/$w;

        $tHMax = NoteMaree::getTAfterBegin($tHMax, $periode, $tBegin);
        $tHMin = NoteMaree::getTAfterBegin($tHMin, $periode, $tBegin);

        return array('a'=>$a, 'b'=>$b, 'w'=>$w, 'phi'=>$phi, 'yMax'=>$yMax, 'yMin'=>$yMin,
                    'periode'=>$periode, 'tyMin'=>$tHMin, 'tyMax'=>$tHMax);
    }

    /**
     * @param $tabDataSinu
     * @param $t: t au point y de la courbe
     * @param $y: hauteur
     * return vraie si 60 s plus tard, y(t+60) est plus grand que $y -> courbe montante

    static function isMontant($tabDataSinu, $t, $y) {

        // courbe montante ou descendante ?
        $a=$tabDataSinu['a'];
        $b=$tabDataSinu['b'];
        $phi=$tabDataSinu['phi'];
        $w=$tabDataSinu['w'];

        $yPlusTard=$a*sin($w*($t+60)+$phi)+$b; // y: 60 sec plus tard

        return $yPlusTard>$y;
            // courbe montante
    }

    /**
     * @param $tabDataSinu
     * @param $tInter
     * @param $tBegin
     * @param $y
     * @return array of $k and new value of tItner
     * Le but est de trouver la 1er intersection après tBegin

    static function findTInterBegin($tabDataSinu, $k, $tInter, $tBegin, $y) {
        if ($tInter<$tBegin) {
            $tInter=NoteMaree::getInter($tabDataSinu, $k, $y);
            if ($tInter<$tBegin) {
                $k++;
                return NoteMaree::findTInterBegin($tabDataSinu, $k, $tInter, $tBegin, $y);
            }
        }
        return array($k, $tInter);
    }

    /**
     * @param $tInter1
     * @param $tInter2
     * @param $tEnd
     * @param $timeRestriction
     * @param $timeTab
     *
     * Calcul le temps de restriction, entre $tInterFirst et $tInterSecond

    static function calculTimeRestrictionBetweenTInterFirstAndTInterSecond($tInterFirst, $tInterSecond, $tEnd, $timeRestriction, $timeTab) {
        if ($tInterSecond<=$tEnd) {
            $timeRestriction += $tInterSecond-$tInterFirst;
            $timeTab[]=array("begin"=>date("H:i:s", $tInterFirst), "end"=>date("H:i:s", $tInterSecond));
        } elseif ($tInterFirst<$tEnd) {
            //$tEnd entre $tInterMax et $tInterMin
            $timeRestriction += $tEnd-$tInterFirst;
            $timeTab[]=array("begin"=>date("H:i:s", $tInterFirst), "end"=>date("H:i:s", $tEnd));
        }
        return array($timeRestriction, $timeTab);
    }

    /**
     * On est calé sur $tInterMin en pente montante
     * On calcul l'ensemble du temps de la restriction avec restriction Max et restriction Min

    static function calculTimeRestrictionFromTInterMin($tInterMin, $periode, $timeToAdd, $timeInter2tInterMax, $tEnd, $timeRestriction, $timeTab) {
        $t=$tInterMin;
        if ($t<$tEnd) {
            if ($t+$timeToAdd<=$tEnd) {
                // cas normal on ajoute $timeToAdd
                $timeRestriction += $timeToAdd;
                $timeTab[]=array("begin"=>date("H:i:s", $t), "end"=>date("H:i:s", $t+$timeToAdd));

                $t = $t+$timeToAdd;
                // $t = $tInterMax

                // On va jusqu'à l'autre $tInterMax (pente descendante)
                $t=$t+$timeInter2tInterMax;
                if ($t+$timeToAdd<=$tEnd) {
                    // On peut ajouter $timeToAdd
                    $timeRestriction += $timeToAdd;
                    $timeTab[]=array("begin"=>date("H:i:s", $t), "end"=>date("H:i:s", $t+$timeToAdd));

                    $t=$t+$timeToAdd;
                    //$t est sur $tInterMin pente descendente
                    $t=$tInterMin+$periode;
                    // $t est sur $tinterMin pente montante
                    return NoteMaree::calculTimeRestrictionFromTInterMin($t,$periode, $timeToAdd, $timeInter2tInterMax, $tEnd, $timeRestriction, $timeTab);
                } else {
                    //$t+$timeToAdd>$tEnd
                    if ($t<$tEnd) {
                        // $tEnd est entre $tInterMax et $tInterMin, cad entre $t et $t+$timeToAdd (pente descendante)
                        $timeRestriction += $tEnd-$t;
                        $timeTab[]=array("begin"=>date("H:i:s", $t), "end"=>date("H:i:s", $tEnd));
                    }
                    return array($timeRestriction, $timeTab);
                }
            } else {
                // $tEnd est entre $tInterMin et $tInterMax, cad entre $t et $t+$timeToAdd (pente montante)
                $timeRestriction += $tEnd-$t;
                $timeTab[]=array("begin"=>date("H:i:s", $t), "end"=>date("H:i:s", $tEnd));
                return array($timeRestriction, $timeTab);
            }
        } else {
            return array($timeRestriction, $timeTab);
        }
    }

    static function getTAfterBegin($t, $periode, $tBegin) {
        if ($t>=$tBegin) {
            return $t;
        } else {
            $t = $t+$periode;
            return NoteMaree::getTAfterBegin($t, $periode, $tBegin);
        }
    }
     */
} 