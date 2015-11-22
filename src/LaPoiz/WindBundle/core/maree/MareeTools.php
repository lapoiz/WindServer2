<?php

namespace LaPoiz\WindBundle\core\maree;

use LaPoiz\WindBundle\Command\CreateNbHoureCommand;
use LaPoiz\WindBundle\Command\CreateNoteCommand;

class MareeTools {

    /**
     * @param $spot: spot dont il est question -> pour récuperer les restrictions
     * @param $tabHoure : array $tabHoure[$nbHoure] for the day
     * @param $mareeDate : prévision de marre que l'on va analyser
     * Remplis le $tabHoure avec le temps de navigation possible pour chaque heure=elem du tab
     */
    static function calculNbHoureNav($spot, &$tabHoure, $mareeDate) {
        $timeRestriction=0;
        $timeTab = array();

        $listePrevisionMaree=$mareeDate->getListPrevision(); // liste des marées extreme de la journée
        $mareeRestrictionTab = array();
        foreach ($spot->getMareeRestriction() as $mareeRestriction) {
            $restriction=array();
            $restriction['hMin']=$mareeRestriction->getHauteurMin();
            $restriction['hMax']=$mareeRestriction->getHauteurMax();
            $restriction['state']=$mareeRestriction->getState();
            $mareeRestrictionTab[]=$restriction;
        }

        $tabDataSinu = self::generateSinousoidal($listePrevisionMaree);
        $date = $listePrevisionMaree->first()->getMareeDate()->getDatePrev();


        // On avance d'heure en heure en comparant y aux restrictions
        // lorsque intersection avec une restriction -> arrondi (la courbe ne correspond pas tout a fait à la réalité)

        for ($houre=CreateNbHoureCommand::HEURE_MATIN; $houre<=CreateNbHoureCommand::HEURE_SOIR; $houre++) {
            // y = a  sin(wt + Phi) + b
            // *** Calcul de la formule de la courbe: y = a  sin(wt + Phi) + b ***
            // t=time en seconde, y=hauteur en metre, w: phase 2 pi / T , T: fréquence
            $hight=MareeTools::getHight($tabDataSinu,$date,$houre+0.5);
            $tabHoure[$houre]['marée']=MareeTools::getNbHoureNav($hight,$mareeRestrictionTab);
        }
    }

    /**
     * y = a  sin(wt + Phi) + b
     */

    static function getHight($tabDataSinu,$date,$hour) {
        $a=$tabDataSinu['a'];
        $b=$tabDataSinu['b'];
        $phi=$tabDataSinu['phi'];
        $w=$tabDataSinu['w'];
        //$t=$hour*60*60; // t on second
        $t=$date->setTime($hour, '0', '0')->getTimestamp();

        return $a*sin($w*($t+60)+$phi)+$b;
    }

    /**
     * @param $hight: hight of the sea
     * @param $mareeRestrictionTab: tab with all ['hMin', 'hMax','state'] of this spot
     * @return number of houre you can navigate -> KO=>0 , Warn=>0.5, OK=>1
     */
    static function getNbHoureNav($hight,$mareeRestrictionTab) {
        $nbHoure=0;
        foreach ($mareeRestrictionTab as $restriction){
            if ($hight>$restriction['hMin'] && $hight<=$restriction['hMax']) {
                if ($restriction['state']=="OK") {
                    $nbHoure=1;
                } elseif($restriction['state']=="warn") {
                    $nbHoure=0.5;
                }
            }
        }
        return $nbHoure;
    }

    /**
     * @param $listePrevisionMaree
     * @return array
     */
    public static function generateSinousoidal($listePrevisionMaree)
    {
        $tBegin=CreateNbHoureCommand::HEURE_MATIN*60*60; // en seconde
        $tabDataSinu = MareeTools::buildSinusoidal($listePrevisionMaree, $tBegin);
        return $tabDataSinu;
    }

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

        $phi = asin(round(($y1-$b)/$a,10))-$w*$t1; // calcul directement le bon phi = k * phi' (en tous cas proche de t1)

        $periode = 2*pi()/$w;

        $tHMax = MareeTools::getTAfterBegin($tHMax, $periode, $tBegin);
        $tHMin = MareeTools::getTAfterBegin($tHMin, $periode, $tBegin);

        return array('a'=>$a, 'b'=>$b, 'w'=>$w, 'phi'=>$phi, 'yMax'=>$yMax, 'yMin'=>$yMin,
            'periode'=>$periode, 'tyMin'=>$tHMin, 'tyMax'=>$tHMax);
    }

    /**
     * @param $previsionMaree: element contenant la date et time
     * return : la valeur en seconde (des axes des x)
     */
    static function getXTime($previsionMaree) {
        $dateMaree=$previsionMaree->getMareeDate()->getDatePrev();
        $dateMaree->setTime($previsionMaree->getTime()->format('H'), $previsionMaree->getTime()->format('i'), $previsionMaree->getTime()->format('s'));
        return $dateMaree->getTimestamp();
        //return $previsionMaree->getTime()->format('H')*60*60 + $previsionMaree->getTime()->format('i')*60 + $previsionMaree->getTime()->format('s');
    }

    static function getTAfterBegin($t, $periode, $tBegin) {
        if ($t>=$tBegin) {
            return $t;
        } else {
            $t = $t+$periode;
            return MareeTools::getTAfterBegin($t, $periode, $tBegin);
        }
    }






// UTILE POUR l'AFFICHAGE PRINCIPAL : http://localhost/Wind/web/app_dev.php/spot/1
// En utilisant le JSON: http://localhost/Wind/web/app_dev.php/fo/json/lapoizgraph/plage/maree/spot/1

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

        $hMaxRestriction = $mareeRestriction->getHauteurMax(); // hauteur haute de la restriction
        $hMinRestriction = $mareeRestriction->getHauteurMin(); // hauteur basse de la restriction

        $previsionMaree = $listePrevisionMaree->first();

        $dayBegin = new \DateTime($previsionMaree->getMareeDate()->getDatePrev()->format('Y-m-d'));
        $dayBegin->modify('+' . CreateNoteCommand::HEURE_MATIN . ' hours');
        $dayEnd = new \DateTime($previsionMaree->getMareeDate()->getDatePrev()->format('Y-m-d'));
        $dayEnd->modify('+' . CreateNoteCommand::HEURE_SOIR . ' hours');

        $tBegin = $dayBegin->getTimestamp(); // jour J à 8h00
        $tEnd = $dayEnd->getTimestamp();     // jour J à 20h00

        $tabDataSinu = self::generateSinousoidal($listePrevisionMaree);       // hauteur min de la sinousoidal
        $yMax=$tabDataSinu['yMax'];
        $yMin=$tabDataSinu['yMin'];
        $periode=$tabDataSinu['periode'];
        $tyMin=$tabDataSinu['tyMin'];
        $tyMax=$tabDataSinu['tyMax'];

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

                    // Calcul point d'intersection suivant

                    if ($tInter>=$tyMax) {
                        // point d'intersection avant la 1er marée haute -> (tHauteurMax-$tInterReel) x 2
                        $timeToAdd=($tyMax+$periode-$tInter)*2;
                    } else {
                        // point d'intersection après la 1er marée haute -> (tHauteurMax-$tInterReel) x 2
                        $timeToAdd=($tyMax-$tInter)*2;
                    }

                    //$timeRestriction +=$timeToAdd;
                    // $tInter est au debut d'une periode à prendre en compte
                } else {
                    // courbe descendante
                    // on prend de tBegin à tInter
                    $timeRestriction += $tInter-$tBegin;
                    $timeTab[]=array("begin"=>date("H:i:s", $tBegin), "end"=>date("H:i:s", $tInter));

                    // calcul le temps à ajouter pour chaque période
                    if ($tInter<=$tyMin) {
                        // t intersection avec restriction est avant la 1er marée basse
                        $timeToAdd = $periode - ($tyMin - $tInter)*2;
                        $tInter=$tInter+($tyMin - $tInter)*2;// $tInter est au debut d'une periode à prendre en compte
                    } else {
                        // t intersection avec restriction est après la 1er marée basse
                        $timeToAdd = $periode - ($tyMin + $periode - $tInter)*2;
                        $tInter=$tInter+($tyMin + $periode - $tInter)*2;// $tInter est au debut d'une periode à prendre en compte
                    }
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
                        if ($tInter<=$tyMax) {
                            // t intersextion est avant la 1er marée haute
                            $timeToAdd = $periode - ($tyMax - $tInter)*2;
                            $tInter = $tInter+($tyMax - $tInter)*2; // $tInter: debut d'un interval à compter
                        } else {
                            // t intersextion est après la 1er marée haute
                            $timeToAdd = $periode - ($tyMax + $periode - $tInter)*2;
                            $tInter = $tInter+($tyMax + $periode - $tInter)*2; // $tInter: debut d'un interval à compter
                        }

                    } else {
                        // courbe descendante
                        //on prend tous ce qui suit, jusqu'au point d'intersection suivant
                        if ($tInter<=$tyMin) {
                            // t Intersection avec restriction max, est avant la 1er marée basse de la journnée
                            $timeToAdd=($tyMin-$tInter)*2;
                        } else {
                            // la 1er marée basse de la journnée, est avant t Intersection avec restriction max
                            $timeToAdd=($tyMin+$periode-$tInter)*2;
                        }
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
     * @param $tabDataSinu: tableau des valeur de la sinousoidal
     * @param $k : facteur période de la courbe
     * @param $yInter: valeur y de l'intersection
     * @return l'heure de l'intersection (x ~ t)
     */
    static function getInter($tabDataSinu, $k, $yInter) {
        $y=doubleval($yInter);
        $b=$tabDataSinu['b'];
        $a=$tabDataSinu['a'];
        $phi=$tabDataSinu['phi'];
        $w=$tabDataSinu['w'];

        $tInter=0;


        if ($k % 2 == 0) {
            //$k paire
            $k= ($k == 0 ? 0 : $k/2);
            $tInter=(asin(round(($y-$b)/$a,10))- $phi + $k*2*pi())/$w;
        } else {
            //$k impaire -> -pi
            $k=$k-1;
            $k= ($k == 0 ? 0 : $k/2);
            $tInter=(pi()-asin(round(($y-$b)/$a,10))- $phi + $k*2*pi())/$w;
        }
        return $tInter;
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

}