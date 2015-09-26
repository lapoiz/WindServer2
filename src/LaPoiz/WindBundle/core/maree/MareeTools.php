<?php

namespace LaPoiz\WindBundle\core\maree;

use LaPoiz\WindBundle\Command\CreateNoteCommand;

class MareeTools {
    static function buildSinusoidal($listePrevisionMaree, $tBegin) {
        // Récupére les 2 premiers points
        $previsionMaree1 = $listePrevisionMaree->first();
        $previsionMaree2 = $listePrevisionMaree->next();
        $t1=MareeTools::getXTime($previsionMaree1);
        $y1=$previsionMaree1->getHauteur();
        $t2=MareeTools::getXTime($previsionMaree2);
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

        $tHMax = MareeTools::getTAfterBegin($tHMax, $periode, $tBegin);
        $tHMin = MareeTools::getTAfterBegin($tHMin, $periode, $tBegin);

        return array('a'=>$a, 'b'=>$b, 'w'=>$w, 'phi'=>$phi, 'yMax'=>$yMax, 'yMin'=>$yMin,
            'periode'=>$periode, 'tyMin'=>$tHMin, 'tyMax'=>$tHMax);
    }


    /**
     * @param $mareeRestriction: points de la restriction
     * @param $a: de l'équation y = a  sin(wt + Phi) + b
     * @param $b: de l'équation y = a  sin(wt + Phi) + b
     * @param $w: de l'équation y = a  sin(wt + Phi) + b
     * @param $phi: de l'équation y = a  sin(wt + Phi) + b
     * @param $tHMax: time lorsque la courbe est au + haut pour la 1er fois de la journée
     * Return tableau et le temps (en sec) correspondant à cette restriction entre HEURE_MATIN et HEURE_SOIR
     */
    static function calculTimeInState($mareeRestriction, $listePrevisionMaree) {
        $timeRestriction=0;
        $timeTab = array();

        list($hMaxRestriction, $hMinRestriction, $tBegin, $tEnd, $tabDataSinu, $periode, $tyMax, $tyMin, $yMax, $yMin) = self::generateSinousoidal($mareeRestriction, $listePrevisionMaree);       // hauteur min de la sinousoidal


        if ($hMaxRestriction>=$yMax) {
            // Courbe au dessous de la restriction: $hMaxRestriction
            // -> tous ce qui est au dessus de $hmin est à comptabiliser
            if ($hMinRestriction<=$yMin) {
                // Courbe au dessus de la restriction: $hMinRestriction => Aucun interet d'avoir une restriction...
                // -> tous est à comptabiliser
                $timeRestriction=$tEnd-$tBegin;
                $timeTab[]=array("begin"=>date("H:i:s", $tBegin), "end"=>date("H:i:s", $tEnd));
            } else {
                // courbe au dessous de la restriction max et min -> intersection entre la restriction min et la courbe
                $c=$hMinRestriction; // intersection avec la droite $y=$c
                //tous ce qui est au dessus de $hmin est à comptabiliser ($hMin coupe la courbe sinusoidale de la marée)

                list($k, $tInter) = MareeTools::findTInterBegin($tabDataSinu, 0, 0, $tBegin, $c);

                // courbe montante ou descendante ?

                if (MareeTools::isMontant($tabDataSinu, $tInter, $c)) {
                    // courbe montante
                    //on prend tous ce qui suit, jusqu'au point d'intersection suivant

                    // Calcul point d'intersection suivant: (tHauteurMax-$tInterReel) x 2
                    $timeToAdd=($tyMax-$tInter)*2;
                    //$timeRestriction +=$timeToAdd;
                    // $tInter est au debut d'une periode à prendre en compte
                } else {
                    // courbe descendante
                    // on prend de tBegin à tInter
                    $timeRestriction += $tInter-$tBegin;
                    $timeTab[]=array("begin"=>date("H:i:s", $tBegin), "end"=>date("H:i:s", $tInter));

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
                    list($k, $tInter) = MareeTools::findTInterBegin($tabDataSinu, 0, 0, $tBegin, $c);


                    if (MareeTools::isMontant($tabDataSinu, $tInter, $c)) {
                        // courbe ascendante
                        // on prend de tBegin à tInter
                        $timeRestriction += $tInter-$tBegin;
                        $timeTab[]=array("begin"=>date("H:i:s", $tBegin), "end"=>date("H:i:s", $tInter));

                        // calcul le temps à ajouter pour chaque période
                        $timeToAdd = $periode - ($tyMax - $tInter)*2;
                        $tInter = $tInter+($tyMax - $tInter)*2; // $tInter: debut d'un interval à compter
                    } else {
                        // courbe descendante
                        //on prend tous ce qui suit, jusqu'au point d'intersection suivant

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
                                //***************** ICI EST l ERREUR *********************
                            }
                        }
                    }
                } else {
                    // pire des cas intersection pour partie haute et partie basse de la restriction....
                    // On se cale sur tInterMin en pente montante

                    // Find intersection after $tBegin
                    list($kMax, $tInterMax) = MareeTools::findTInterBegin($tabDataSinu, 0, 0, $tBegin, $hMaxRestriction);
                    list($kMin, $tInterMin) = MareeTools::findTInterBegin($tabDataSinu, 0, 0, $tBegin, $hMinRestriction);

                    // $tInterMin IS NOT GOD - Après $tInterMax en monté -> pas normal

                    $timeToAdd=0; // temps entre $tInterMin et $tInterMax en phase montante
                    $timeInter2tInterMax = 0; // temps entre les 2 tInterMax (pente montante et pente descendante)

                    if ($tInterMax<$tInterMin) {
                        // 1er intersection: $tInterMax -> descendante ou tBegin entre les deux
                        if (MareeTools::isMontant($tabDataSinu, $tInterMin, $hMinRestriction)) {
                            // Ajoute $tBegin -> $tInterMax
                            list ($timeRestriction, $timeTab) = MareeTools::calculTimeRestrictionBetweenTInterFirstAndTInterSecond($tBegin, $tInterMax, $tEnd, $timeRestriction, $timeTab);

                            // Chercher l'intersection suivante, symetrique par rapport à $tHMax
                            $timeInter2tInterMax = ($tyMax-$tInterMax)*2;
                            $tInterMax=$tInterMax+$timeInter2tInterMax;

                            // calcul entre $tInterMax et $tInterMin
                            list ($timeRestriction, $timeTab) = MareeTools::calculTimeRestrictionBetweenTInterFirstAndTInterSecond($tInterMax, $tInterMin, $tEnd, $timeRestriction, $timeTab);
                            $timeToAdd = $tInterMin-$tInterMax;

                            // On se cale sur tInterMin en pente montante
                            $tInterMin = $tInterMin+($tyMin-$tInterMin)*2;
                        } else {
                            // pente descendante
                            // calcul entre $tInterMax et $tInterMin
                            list ($timeRestriction, $timeTab) = MareeTools::calculTimeRestrictionBetweenTInterFirstAndTInterSecond($tInterMax, $tInterMin, $tEnd, $timeRestriction, $timeTab);
                            $timeToAdd = $tInterMin-$tInterMax;

                            $timeInter2tInterMin=($tyMin-$tInterMin)*2;
                            $timeInter2tInterMax=$periode-2*$timeToAdd-$timeInter2tInterMin; //prend un crayon pour t'en assurer...

                            // On se cale sur tInterMin en pente montante
                            $tInterMin = $tInterMin+$timeInter2tInterMin;
                        }
                    } else {
                        // $tInterMin<$tInterMax
                        // 1er intersection: $tInterMin -> montante ou tBegin entre les deux
                        if (MareeTools::isMontant($tabDataSinu, $tInterMin, $hMinRestriction)) {
                            // On est calé sur tInterMin en pente montante
                            // Nickel on ne fait rien
                            $timeToAdd = $tInterMax-$tInterMin;
                            // On est en pente montante, tInterMax est l'interception avec restriction haute et la courbe
                            // -> prochaine interception courbe descendante symétrique avec le sommet qui est en tyMax
                            $timeInter2tInterMax=2*($tyMax-$tInterMax); //prend un crayon pour t'en assurer...
                            //$timeInter2tInterMax=2*($timeToAdd+$tyMax-$tInterMin); //prend un crayon pour t'en assurer...Mais c'est faux...

                        } else {
                            // pente descendante

                            // add $tBegin -> $tInterMin
                            list ($timeRestriction, $timeTab) = MareeTools::calculTimeRestrictionBetweenTInterFirstAndTInterSecond($tBegin, $tInterMin, $tEnd, $timeRestriction, $timeTab);

                            // On se cale sur tInterMin en pente montante

                            // aller à l'intersection suivante - symetrique avec THMin
                            $timeInter2tInterMin=($tyMin-$tInterMin)*2;
                            $tInterMin = $tInterMin + $timeInter2tInterMin;
                            $timeToAdd = $tInterMax-$tInterMin;
                            $timeInter2tInterMax=$periode-2*$timeToAdd-$timeInter2tInterMin; //prend un crayon pour t'en assurer... Mais c'est faux ???
                        }
                    }

                    // $timeToAdd, $timeInter2tInterMax et $epriode sont définits
                    // On est calé sur tInterMin en pente montante
                    list ($timeRestriction, $timeTab) = MareeTools::calculTimeRestrictionFromTInterMin($tInterMin, $periode, $timeToAdd, $timeInter2tInterMax, $tEnd, $timeRestriction, $timeTab);
                }
            }
        }

        return array($timeRestriction, $timeTab);
    }


    /**
     * @param $tabDataSinu: tableau des données de la sinusoidal
     * @param $tInter: time de la dernier intersection
     * @param $tBegin: temps à partir duquel on regarde lorsqu'il y a intersection
     * @param $y : ligne de l'intersection avec la sinousoidal
     * @return array of $k and new value of tInter
     * Le but est de trouver la 1er intersection après tBegin
     */
    static function findTInterBegin($tabDataSinu, $k, $tInter, $tBegin, $y) {
        if ($tInter<$tBegin) {
            $tInter=MareeTools::getInter($tabDataSinu, $k, $y);
            if ($tInter<$tBegin) {
                $k++;
                return MareeTools::findTInterBegin($tabDataSinu, $k, $tInter, $tBegin, $y);
            }
        }
        return array($k, $tInter);
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
                    return MareeTools::calculTimeRestrictionFromTInterMin($t,$periode, $timeToAdd, $timeInter2tInterMax, $tEnd, $timeRestriction, $timeTab);
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
            return MareeTools::getTAfterBegin($t, $periode, $tBegin);
        }
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
     * @param $tabDataSinu: tableau des valeur de la sinousoidal
     * @param $k : facteur période de la courbe
     * @param $yInter: valeur y de l'intersection
     * @return l'heure de l'intersection (x)
     */
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
     * @param $mareeRestriction
     * @param $listePrevisionMaree
     * @return array
     */
    public static function generateSinousoidal($mareeRestriction, $listePrevisionMaree)
    {
        $hMaxRestriction = $mareeRestriction->getHauteurMax(); // hauteur haute de la restriction
        $hMinRestriction = $mareeRestriction->getHauteurMin(); // hauteur basse de la restriction

        $previsionMaree = $listePrevisionMaree->first();

        $dayBegin = new \DateTime($previsionMaree->getMareeDate()->getDatePrev()->format('Y-m-d'));
        $dayBegin->modify('+' . CreateNoteCommand::HEURE_MATIN . ' hours');
        $dayEnd = new \DateTime($previsionMaree->getMareeDate()->getDatePrev()->format('Y-m-d'));
        $dayEnd->modify('+' . CreateNoteCommand::HEURE_SOIR . ' hours');

        $tBegin = $dayBegin->getTimestamp(); // jour J à 8h00
        $tEnd = $dayEnd->getTimestamp();     // jour J à 20h00

        $tabDataSinu = MareeTools::buildSinusoidal($listePrevisionMaree, $tBegin);
        $periode = $tabDataSinu['periode']; //  Periode de la sinousoidal
        $tyMax = $tabDataSinu['tyMax'];     // heure lorsque hauteur max de la sinousoidal après 8h
        $tyMin = $tabDataSinu['tyMin'];     // heure lorsque hauteur min de la sinousoidal après 8h
        $yMax = $tabDataSinu['yMax'];       // hauteur max de la sinousoidal
        $yMin = $tabDataSinu['yMin'];
        return array($hMaxRestriction, $hMinRestriction, $tBegin, $tEnd, $tabDataSinu, $periode, $tyMax, $tyMin, $yMax, $yMin);
    }
}