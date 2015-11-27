<?php

namespace LaPoiz\WindBundle\core\maree;

use Goutte\Client;
use LaPoiz\WindBundle\Entity\MareeDate;
use LaPoiz\WindBundle\Entity\PrevisionMaree;


class MareeGetData {

    static function getMaree($mareeInfoURL) {
        // city from http://maree.info/idSpotURL

        $client = new Client();
        $crawler = $client->request('GET', $mareeInfoURL);

        $prevMaree = array();

        for ($numJour = 0; $numJour <= 6; $numJour++) {
            $trMaree = $crawler->filter('#MareeJours_'.$numJour);
            $prevMaree[$numJour]=MareeGetData::getElemMareeFromTr($trMaree);
        }

        return $prevMaree;
    }


    /**
     * @param $mareeInfoURL: URL de info maréé, ex: http://maree.info/16
     * @param $nbDays: number of prevision days since today
     * @return an array of prevision
     */
    static function getMareeForXDays($mareeInfoURL, $nbDays, $output) {

        $prevMaree = array();
        $day = new \DateTime("now");

        for ($numJour = 0; $numJour < $nbDays; $numJour++) {
            // $url=$mareeInfoURL?d=$idDateURLInfoMaree
            //$idDateURLInfoMaree = 20150106
            $url=$mareeInfoURL.'?d='.$day->format('Ymd');
            $output->writeln('<info>url : '.$url.'</info>');
            $prevMaree[$numJour]=MareeGetData::getHauteurMareeFromURL($url);
            $day->add(new \DateInterval('P01D')); // ajout d'un jour
        }

        return $prevMaree;
    }

    static function saveMaree($spot, $prevMaree, $entityManager, $output) {
        $output->writeln('<info>****** function saveMaree ****</info>');
        $today = new \DateTime("now");
        $currentDay=$today;
        $regExGetHoure = '#h#';
        $regExGetHauteur = '#m#';

        $lastMareeDate = $entityManager->getRepository('LaPoizWindBundle:MareeDate')->findLast($spot);
        $beginDate = null;
        if ($lastMareeDate != null && !$lastMareeDate->getListPrevision()->isEmpty( )) {
            $beginDate = date_add($lastMareeDate->getDatePrev(), new \DateInterval('P1D'));// DatePrev est à 00h00m00s -> jour +1 pour comparaison
        }

        foreach ($prevMaree as $numDay => $jour) {
            // $jour=0 puis 1 ...
            if ($beginDate ==null || $currentDay>$beginDate) {
                $mareeDate = new MareeDate();
                $mareeDate->setDatePrev($currentDay);

                $mareeDate->setSpot($spot);
                foreach ($jour as $heure => $hauteur) {
                    $previsionMaree = new PrevisionMaree();

                    list($hauteurPrev) = preg_split( $regExGetHauteur, $hauteur);
                    $previsionMaree->setHauteur(floatval($hauteurPrev));
                    $hour=new \DateTime();
                    //$hour->modify("+".$numDay." days");
                    $output->writeln('$heure: '.$heure);
                    $output->writeln('$hauteurPrev: '.$hauteurPrev);
                    // $heure = 17h40
                    //list($hourPrev,$minPrev) = preg_split( $regExGetHoure, $heure);
                    list($hourPrev,$minPrev) = preg_split( $regExGetHoure, $heure);
                    $hour->setTime(intval($hourPrev), intval($minPrev));
                    $previsionMaree->setTime($hour);
                    $previsionMaree->setMareeDate($mareeDate);
                    $mareeDate->addListPrevision($previsionMaree);
                    $entityManager->persist($previsionMaree);
                }
                $output->writeln('$mareeDate->getDatePrev 1: '.$mareeDate->getDatePrev()->format('Y-m-d H:i:s'));
                $entityManager->persist($mareeDate);
                $entityManager->flush();
                $output->writeln('$mareeDate->getDatePrev 2: '.$mareeDate->getDatePrev()->format('Y-m-d H:i:s'));
            } // end of if $currentDay>$beginDate

            $currentDay= date_add($currentDay, new \DateInterval('P1D'));
        };
        $entityManager->flush();
    }

    static function deleteMaree($spot, $entityManager, $output) {
        $output->writeln('<info>****** function deleteMaree ****</info>');

        foreach ($spot->getListMareeDate() as $mareeDate) {
                $output->writeln('<info>delete $mareeDate->getDatePrev : '.$mareeDate->getDatePrev()->format('Y-m-d H:i:s').'</info>');
                try {
                    $entityManager->remove($mareeDate);
                    $entityManager->flush();
                } catch (\Exception $ex) {
                    $output->writeln("Exception Found - " . $ex->getMessage());;
                }
        }
    }


    /*
     * scan la page des marées sur 2 mois.
     * Puis récupére la date de la marée la plus basse, de la marée normal (coef 80) et de la plus haute marée
     * Return array : {
     *
     */
    static function getExtremMaree($idSpotMareeInfo) {
        // Récupere la liste des amplitudes de marée: http://maree.info/$idURLInfoMaree/calendrier
        // Parse le tableau et récupére URL de la marée à coef le plus haut, de la marée le coef le plus bas, et de la marée à coef 80
        // sur la base de :
        // Tous les Table classe="CalendrierMois"
        //  Pour chaque TR
        //      récupére les TD class="coef"
        //          compare avec max, min et 80
        //          si OK
        //              get id de TD class="event" (en enlevant le D du début)
        //              get title de TD class="DW"

        $urlPage = "http://maree.info/".$idSpotMareeInfo."/calendrier";

        $client = new Client();
        $crawler = $client->request('GET', $urlPage);
        $status_code = $client->getResponse()->getStatus();
        if ($status_code == 200) {
            // Boucle dans tous les class="CalendrierMois"
            $mois = $crawler->filter('.CalendrierMois')->each(function ($nodeMonth, $iMonth) {
                // Boucle dans tous les tr de class="CalendrierMois"
                $day = $nodeMonth->filter('tr')->each(function ($nodeDay, $iDay) {
                        if ($iDay>0) {
                            $coef = $nodeDay->filter('.Coef')->each(function ($nodeCoef, $iCoef) {
                                    return $nodeCoef->text();
                            });
                            $dateMaree=$nodeDay->filter('.Event');
                            $regExGetIdURL = '#[0-9]+#';
                            $idURL="";
                            // D20153011 -> 20153011
                            if (preg_match($regExGetIdURL,$dateMaree->attr('id'),$value)>0) {
                                $idURL = $value[0];
                            }
                            return ["coefs" => $coef,
                                    "date" => $dateMaree->attr('title'),
                                    "idDateUrl"=> $idURL
                            ];
                        }
                    });
                    return $day;
            });

            $coefMax = 0;
            $coefMin = 200;
            $coefNor = 80;
            $coefNorBis = 79; // Si pas de coef 80 durant 4 mois d'affilés (on ne sait jamais) ...
            $dataExtrem = array();

            // Récupére du tableau de tous les coef, que les extremes et le coef normal
            foreach ($mois as $unMois) {
                foreach ($unMois as $day) {
                    if ($day!=null) {

                            foreach ($day["coefs"] as $coef) {
                                if ($coef==$coefNor) {
                                    $day["coef"]=$coef;
                                    $dataExtrem["norm"]= $day;
                                } elseif ($coef==$coefNorBis) {
                                    $day["coef"]=$coef;
                                    $dataExtrem["normBis"]= $day;
                                } elseif ($coef > $coefMax) {
                                    $coefMax = $coef;
                                    $day["coef"]=$coef;
                                    $dataExtrem["max"]= $day;
                                } elseif ($coef < $coefMin) {
                                    $coefMin = $coef;
                                    $day["coef"]=$coef;
                                    $dataExtrem["min"]= $day;
                                }
                            }
                    }
                }
            }
            return $dataExtrem;
        } else {
            return null;
        }
    }

    static function getHauteurMareeFromURL($url) {
        // Récupere la page: $url=http://maree.info/$idURLInfoMaree?d=$idDateURLInfoMaree
        // $idDateURLInfoMaree = 20150106

        // Parse avec ce qui est déjà fait dans MareeGetData::getMaree
        // envoie la hauteur marée haute et marée basse
        $client = new Client();
        $crawler = $client->request('GET', $url);
        $trMaree = $crawler->filter('#MareeJours_0');// premier elem car on arrive direct sur le bon jour

        return MareeGetData::getElemMareeFromTr($trMaree);
    }

    static function getHauteurMaree($idURLInfoMaree, $idDateURLInfoMaree) {
        // Récupere la page: http://maree.info/$idURLInfoMaree?d=$idDateURLInfoMaree
        // dateMaree = 20150106
        $url = "http://maree.info/".$idURLInfoMaree."?d=".$idDateURLInfoMaree;
        return MareeGetData::getHauteurMareeFromURL($url);
    }

    static function getElemMareeFromTr($trMaree) {

        $regExGetTD = '#<[^>]*[^\/]>#i';
        $regExGetHeure = '#h#';
        $regExGetHauteur = '#[0-9]+m#';

        $trHMTL = $trMaree->html();
        //<th><a href="/16" onmouseover="QSr(this,'?d=201408121');" onclick="this.onmouseover();this.blur();return false;">Mer.<br><b>13</b></a></th>
        //<td><b>01h53</b><br>08h34<br><b>14h18</b><br>20h55</td>
        //<td><b>9,00m</b><br>0,60m<br><b>8,85m</b><br>0,80m</td>
        //<td><b>113</b><br> <br><b>112</b><br> </td>
        //$regExGetTD = '#<td>(.*)<\/td>#is';

        $elemHTML = preg_split( $regExGetTD, $trHMTL, -1, PREG_SPLIT_NO_EMPTY);
        $prevMaree=array();
        $tabHeureMaree = array();
        $numVal=0;
        foreach ($elemHTML as $elem) {
            if (preg_match($regExGetHeure,$elem)) {
                $tabHeureMaree[]= $elem;
            } elseif (preg_match($regExGetHauteur,$elem)) {
                $prevMaree[$tabHeureMaree[$numVal]]= str_replace(",",".",$elem);
                $numVal++;
            }
        }
        return $prevMaree;
    }

/* DELETE ? - 28/01/2015
    static function getMareeInfoUrl($city) {

        $urlResult='http://maree.info/';
        switch ($city) {
            case 'Wissant':
                $urlResult=$urlResult.'6';
                break;
            case 'LeCrotoy':
                $urlResult=$urlResult.'150';
                break;
            case 'Dieppe':
                $urlResult=$urlResult.'14';
                break;
            case 'Fécamp':
                $urlResult=$urlResult.'16';
                break;
            case 'Deauville':
                $urlResult=$urlResult.'23';
                break;
            case 'Ouistreham':
                $urlResult=$urlResult.'25';
                break;
            case 'Etretat':
                $urlResult=$urlResult.'17';
                break;
            case 'Saint-Valery-en-Caux':
                $urlResult=$urlResult.'15';
                break;
        }

        return $urlResult;
    }
*/
} 