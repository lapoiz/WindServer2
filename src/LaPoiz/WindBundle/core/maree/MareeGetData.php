<?php

namespace LaPoiz\WindBundle\core\maree;

use Goutte\Client;
use LaPoiz\WindBundle\Entity\MareeDate;
use LaPoiz\WindBundle\Entity\PrevisionMaree;


class MareeGetData {

    static function getMaree($mareeInfoURL) {
        // city from http://maree.info/

        $client = new Client();
        $crawler = $client->request('GET', $mareeInfoURL);

        $prevMaree = array();
        $regExGetTD = '#<[^>]*[^\/]>#i';
        $regExGetHeure = '#h#';
        $regExGetHauteur = '#[0-9]+m#';
        for ($numJour = 0; $numJour <= 6; $numJour++) {
            $trMaree = $crawler->filter('#MareeJours_'.$numJour);

            $trHMTL = $trMaree->html();
            //<th><a href="/16" onmouseover="QSr(this,'?d=201408121');" onclick="this.onmouseover();this.blur();return false;">Mer.<br><b>13</b></a></th>
            //<td><b>01h53</b><br>08h34<br><b>14h18</b><br>20h55</td>
            //<td><b>9,00m</b><br>0,60m<br><b>8,85m</b><br>0,80m</td>
            //<td><b>113</b><br> <br><b>112</b><br> </td>
            //$regExGetTD = '#<td>(.*)<\/td>#is';

            $elemHTML = preg_split( $regExGetTD, $trHMTL, -1, PREG_SPLIT_NO_EMPTY);
            $prevMaree[$numJour]=array();
            $tabHeureMaree = array();
            $numVal=0;
            foreach ($elemHTML as $elem) {
                if (preg_match($regExGetHeure,$elem)) {
                    $tabHeureMaree[]= $elem;
                } elseif (preg_match($regExGetHauteur,$elem)) {
                    $prevMaree[$numJour][$tabHeureMaree[$numVal]]= str_replace(",",".",$elem);
                    $numVal++;
                }
            }
        }

        //return $crawler.html();
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
        if ($lastMareeDate != null) {
            $beginDate = date_add($lastMareeDate->getDatePrev(), new \DateInterval('P1D'));// DatePrev est à 00h00m00s -> jour +1 pour comparaison
        }

        foreach ($prevMaree as $jour) {
            if ($beginDate ==null || $currentDay>$beginDate) {
                $mareeDate = new MareeDate();
                $mareeDate->setDatePrev($currentDay);

                $mareeDate->setSpot($spot);
                foreach ($jour as $heure => $hauteur) {
                    $previsionMaree = new PrevisionMaree();

                    list($hauteurPrev) = preg_split( $regExGetHauteur, $hauteur);
                    $previsionMaree->setHauteur(floatval($hauteurPrev));
                    $hour=new \DateTime();
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


    static function getMarreInfoUrl($city) {

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

} 