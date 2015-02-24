<?php

namespace LaPoiz\WindBundle\core\note;

use LaPoiz\WindBundle\Command\CreateNoteCommand;

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
                    list($timeInState, $timeTab)=NoteMaree::calculTimeInState($mareeRestriction, $listePrevisionMaree);
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






    //////// Functions annexes /////////////////////////////////////////


    /**
     * @param $tabNotes: array $tabNotes['Y-m-d'] for next 7 days
     * @param $datePrev: check if his date is on $tabNotes
     * return true if is in
     */
    static function isInTabNotes($tabNotes, $datePrev) {
        return $datePrev!=null ? is_array($tabNotes[$datePrev->format('Y-m-d')]) : false;
    }

    /**
     * @param $previsionMaree: element contenant la date et time
     * return : la valeur en minute standard (des axes des x)
     */
    static function getXTime($previsionMaree) {
        $day=$previsionMaree->getMareeDate()->getDatePrev();
        $day->setTime($previsionMaree->getTime()->format('H'),$previsionMaree->getTime()->format('i'));
        return $day->getTimestamp();
    }

    /**
     * @param $mareeRestriction: points de la restriction
     * @param $a: de l'équation y = a  sin(wt + Phi) + b
     * @param $b: de l'équation y = a  sin(wt + Phi) + b
     * @param $w: de l'équation y = a  sin(wt + Phi) + b
     * @param $phi: de l'équation y = a  sin(wt + Phi) + b
     * @param $tHMax: time lorsque la courbe est au + haut pour la 1er fois de la journée
     * Return le temps (en sec) correspondant à cette restriction entre HEURE_MATIN et HEURE_SOIR
     */
    static function calculTimeInState($mareeRestriction, $listePrevisionMaree) {
        $timeRestriction=0;
        $timeTab = array();

        $hMaxRestriction=$mareeRestriction->getHauteurMax();
        $hMinRestriction=$mareeRestriction->getHauteurMin();

        $previsionMaree=$listePrevisionMaree->first();

        $dayBegin= new \DateTime($previsionMaree->getMareeDate()->getDatePrev()->format('Y-m-d'));
        $dayBegin->modify('+'.CreateNoteCommand::HEURE_MATIN.' hours');
        $dayEnd= new \DateTime($previsionMaree->getMareeDate()->getDatePrev()->format('Y-m-d'));
        $dayEnd->modify('+'.CreateNoteCommand::HEURE_SOIR.' hours');

        $tBegin=$dayBegin->getTimestamp();
        $tEnd=$dayEnd->getTimestamp();

        $tabDataSinu = NoteMaree::buildSinusoidal($listePrevisionMaree, $tBegin);

        //$hMaxSinu = $tabDataSinu['a']+$tabDataSinu['b'];
        //$hMinSinu = $tabDataSinu['b']-$tabDataSinu['a'];

        $periode = $tabDataSinu['periode'];
        $tyMax = $tabDataSinu['tyMax'];
        $tyMin = $tabDataSinu['tyMin'];
        $yMax = $tabDataSinu['yMax'];
        $yMin = $tabDataSinu['yMin'];


        if ($hMaxRestriction>=$yMax) {
            //tous ce qui est au dessus de $hmin est à comptabiliser
            if ($hMinRestriction<=$yMin) {
                //tous est à comptabiliser
                $timeRestriction=$tEnd-$tBegin;
            } else {
                $c=$hMinRestriction; // intersection avec la droite $y=$c
                //tous ce qui est au dessus de $hmin est à comptabiliser ($hMin coupe la courbe sinusoidale de la marée)

                list($k, $tInter) = NoteMaree::findTInterBegin($tabDataSinu, 0, 0, $tBegin, $c);

                // courbe montante ou descendante ?

                if (NoteMaree::isMontant($tabDataSinu, $tInter, $c)) {
                    // courbe montante
                    //on prend tous ce qui suit, jusqu'au point d'intersection suivant

                    //$tHMax=NoteMaree::getInter($tabDataSinu, $k, $tabDataSinu['ymax']); // $k to the good value
                    //list($kMax, $tHMax) = NoteMaree::findTInterBegin($tabDataSinu, 0, 0, $tBegin, $tabDataSinu['ymax']);

                    // Calcul point d'intersection suivant: (tHauteurMax-$tInterReel) x 2
                    $timeToAdd=($tyMax-$tInter)*2;
                    //$timeRestriction +=$timeToAdd;
                    // $tInter est au debut d'une periode à prendre en compte
                } else {
                    // courbe descendante
                    // on prend de tBegin à tInter
                    $timeRestriction += $tInter-$tBegin;
                    $timeTab[]=array("begin"=>date("H:i:s", $tBegin), "end"=>date("H:i:s", $tInter));

                    //$tHMin = NoteMaree::getInter($a, $b, $w, $phi, $k, $ymin); // $k to the good value
                    //$tHMin = NoteMaree::getInter($tabDataSinu, $k+1, $tabDataSinu['ymin']); // k+1 car une phase plus loin...
                    //list($kMin, $tHMin) = NoteMaree::findTInterBegin($tabDataSinu, 0, 0, $tBegin, $tabDataSinu['ymin']);

                    // calcul le temps à ajouter pour chaque période
                    $timeToAdd = $periode - ($tyMin - $tInter)*2;
                    $tInter=$tInter+($tyMin - $tInter)*2;// $tInter est au debut d'une periode à prendre en compte
                }

                // Calcul combien de fois il faut ajouter ce temps dans la journée (max 3)
                for ($i=0; $i<3; $i++) { // Le premier est déjà ajouté
                    if (($tInter+$timeToAdd+$i*$periode)<=$tEnd) { // on peut ajouter la période
                        $timeRestriction +=$timeToAdd; // ajoute la période
                        $timeTab[]=array("begin"=>date("H:i:s", $tInter+$i*$periode), "end"=>date("H:i:s", ($tInter+$timeToAdd+$i*$periode)));
                    } else {
                        if (($tInter+$i*$periode)<=$tEnd) { // debut de la période OK, mais la fin dépasse l'heure de la fin de la session
                            $timeRestriction += $tEnd-($tInter+$i*$periode);
                            $timeTab[]=array("begin"=>date("H:i:s", ($tInter+$i*$periode)), "end"=>date("H:i:s", $tEnd));
                        }
                    }
                }
            }


        } else {
            // la restriction haute croise la courbe
            if ($hMaxRestriction>$yMin) {
                if ($hMinRestriction<=$yMin) {
                    // tous ce qui est au dessous de $hMaxRestriction est a compter
                    $c=$hMaxRestriction; // intersection avec la droite $y=$c
                    //tous ce qui est au dessus de $hmin est à comptabiliser ($hMin coupe la courbe sinusoidale de la marée)
                    list($k, $tInter) = NoteMaree::findTInterBegin($tabDataSinu, 0, 0, $tBegin, $c);


                    if (NoteMaree::isMontant($tabDataSinu, $tInter, $c)) {
                        // courbe ascendante
                        // on prend de tBegin à tInter
                        $timeRestriction += $tInter-$tBegin;
                        $timeTab[]=array("begin"=>date("H:i:s", $tBegin), "end"=>date("H:i:s", $tInter));

                        //$tHMax = NoteMaree::getInter($tabDataSinu, $k, $tabDataSinu['ymax']); // $k to the good value
                        //list($kMax, $tHMax) = NoteMaree::findTInterBegin($tabDataSinu, 0, 0, $tBegin, $tabDataSinu['ymax']);

                        // calcul le temps à ajouter pour chaque période
                        $timeToAdd = $periode - ($tyMax - $tInter)*2;
                        $tInter = $tInter+($tyMax - $tInter)*2; // $tInter: debut d'un interval à compter
                    } else {
                        // courbe descendante
                        //on prend tous ce qui suit, jusqu'au point d'intersection suivant

                        //$tHMin=NoteMaree::getInter($tabDataSinu, $k+1, $tabDataSinu['ymin']); // $k+1 car une phase plus loin
                        //list($kMin, $tHMin) = NoteMaree::findTInterBegin($tabDataSinu, 0, 0, $tBegin, $tabDataSinu['ymin']);

                        // Calcul point d'intersection suivant: (tHauteurMin-$tInterReel) x 2
                        $timeToAdd=($tyMin-$tInter)*2;
                        // $tInter debut d'un interval à compter
                    }

                    // Calcul combien de fois il faut ajouter ce temps dans la journée (max 3)
                    for ($i=0; $i<4; $i++) {
                        if (($tInter+$timeToAdd+$i*$periode)<=$tEnd) { // on peut ajouter la période
                            $timeRestriction +=$timeToAdd; // ajoute la période
                            $timeTab[]=array("begin"=>date("H:i:s", $tInter+$i*$periode), "end"=>date("H:i:s", ($tInter+$timeToAdd+$i*$periode)));
                        } else {
                            if (($tInter+$i*$periode)<=$tEnd) { // debut de la période OK, mais la fin dépasse l'heure de la fin de la session
                                $timeRestriction += $tEnd-($tInter+$i*$periode);
                                $timeTab[]=array("begin"=>date("H:i:s", ($tInter+$i*$periode)), "end"=>date("H:i:s", $tEnd));
                            }
                        }
                    }

                } else {
                    // pire des cas intersection pour partie haute et partie basse de la restriction....
                    // On se cale sur tInterMin en pente montante

                    // Find intersection after $tBegin
                    list($kMax, $tInterMax) = NoteMaree::findTInterBegin($tabDataSinu, 0, 0, $tBegin, $hMaxRestriction);
                    list($kMin, $tInterMin) = NoteMaree::findTInterBegin($tabDataSinu, 0, 0, $tBegin, $hMinRestriction);

                    // $tInterMin IS NOT GOD - Après $tInterMax en monté -> pas normal

                    //$tHMin = NoteMaree::getInter($tabDataSinu, $kMin, $tabDataSinu['ymin']);
                    //$tHMax=NoteMaree::getInter($tabDataSinu, $kMax, $tabDataSinu['ymax']);
                    //list($kMin, $tHMin) = NoteMaree::findTInterBegin($tabDataSinu, 0, 0, $tBegin, $tabDataSinu['ymin']);
                    //list($kMax, $tHMax) = NoteMaree::findTInterBegin($tabDataSinu, 0, 0, $tBegin, $tabDataSinu['ymax']);

                    $timeToAdd=0; // temps entre $tInterMin et $tInterMax en phase montante
                    $timeInter2tInterMax = 0; // temps entre les 2 tInterMax (pente montante et pente descendante)

                    if ($tInterMax<$tInterMin) {
                        // 1er intersection: $tInterMax -> descendante ou tBegin entre les deux
                        if (NoteMaree::isMontant($tabDataSinu, $tInterMin, $hMinRestriction)) {
                            // Ajoute $tBegin -> $tInterMax
                            list ($timeRestriction, $timeTab) = NoteMaree::calculTimeRestrictionBetweenTInterFirstAndTInterSecond($tBegin, $tInterMax, $tEnd, $timeRestriction, $timeTab);

                            // Chercher l'intersection suivante, symetrique par rapport à $tHMax
                            $timeInter2tInterMax = ($tyMax-$tInterMax)*2;
                            $tInterMax=$tInterMax+$timeInter2tInterMax;

                            // calcul entre $tInterMax et $tInterMin
                            list ($timeRestriction, $timeTab) = NoteMaree::calculTimeRestrictionBetweenTInterFirstAndTInterSecond($tInterMax, $tInterMin, $tEnd, $timeRestriction, $timeTab);
                            $timeToAdd = $tInterMin-$tInterMax;

                            // On se cale sur tInterMin en pente montante
                            $tInterMin = $tInterMin+($tyMin-$tInterMin)*2;
                        } else {
                            // pente descendante
                            // calcul entre $tInterMax et $tInterMin
                            list ($timeRestriction, $timeTab) = NoteMaree::calculTimeRestrictionBetweenTInterFirstAndTInterSecond($tInterMax, $tInterMin, $tEnd, $timeRestriction, $timeTab);
                            $timeToAdd = $tInterMin-$tInterMax;

                            $timeInter2tInterMin=($tyMin-$tInterMin)*2;
                            $timeInter2tInterMax=$periode-2*$timeToAdd-$timeInter2tInterMin; //prend un crayon pour t'en assurer...

                            // On se cale sur tInterMin en pente montante
                            $tInterMin = $tInterMin+$timeInter2tInterMin;
                        }
                    } else {
                        // $tInterMin<$tInterMax
                        // 1er intersection: $tInterMin -> montante ou tBegin entre les deux
                        if (NoteMaree::isMontant($tabDataSinu, $tInterMin, $hMinRestriction)) {
                            // On est calé sur tInterMin en pente montante
                            // Nickel on ne fait rien
                            $timeToAdd = $tInterMax-$tInterMin;
                            $timeInter2tInterMax=2*($timeToAdd+$tyMax-$tInterMin); //prend un crayon pour t'en assurer...

                        } else {
                            // pente descendante

                            // add $tBegin -> $tInterMin
                            list ($timeRestriction, $timeTab) = NoteMaree::calculTimeRestrictionBetweenTInterFirstAndTInterSecond($tBegin, $tInterMin, $tEnd, $timeRestriction, $timeTab);

                            // On se cale sur tInterMin en pente montante

                            // aller à l'intersection suivante - symetrique avec THMin
                            $timeInter2tInterMin=($tyMin-$tInterMin)*2;
                            $tInterMin = $tInterMin + $timeInter2tInterMin;
                            $timeToAdd = $tInterMax-$tInterMin;
                            $timeInter2tInterMax=$periode-2*$timeToAdd-$timeInter2tInterMin; //prend un crayon pour t'en assurer...
                        }
                    }

                    // $timeToAdd, $timeInter2tInterMax et $epriode sont définits
                    // On est calé sur tInterMin en pente montante
                    list ($timeRestriction, $timeTab) = NoteMaree::calculTimeRestrictionFromTInterMin($tInterMin, $periode, $timeToAdd, $timeInter2tInterMax, $tEnd, $timeRestriction, $timeTab);
                }
            }
        }

        return array($timeRestriction, $timeTab);
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
     */
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
     */
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
     */
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
     */
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
} 