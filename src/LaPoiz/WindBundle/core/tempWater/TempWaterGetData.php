<?php

namespace LaPoiz\WindBundle\core\tempWater;

use Goutte\Client;
use LaPoiz\WindBundle\Entity\NotesDate;
use LaPoiz\WindBundle\Entity\PrevisionDate;
use LaPoiz\WindBundle\Entity\PrevisionTempWater;


class TempWaterGetData {

    static function getTempWaterFromSpot($spot, $output) {
        if ($spot->getTempWaterURL() != null) {
            return TempWaterGetData::getTempWater($spot->getTempWaterURL(), $output);
        } else {
            return null;
        }
    }

    /**
     * Récupere les prévision de T°C de l'eau depuis www.meteocity.com sur les 7 prochains joursS
     */
    static function getTempWater($tempWaterInfoURL, $output) {
        // $tempWaterInfoURL: http://www.meteocity.com/france/plage/dieppe_p76217/
        // ajax pour avoir les infos: http://www.meteocity.com/ajax/Beach/ajaxChangeBeachView/?date=20151203+&citId=76217&modificateur=1

        $ajaxUrlType=TempWaterGetData::buildMeteocityAjaxURL($tempWaterInfoURL); // ajaxURL avec "__date__" à remplacer par un truc du type 20151203
        //$regExGetTempWater = '#([0-9]+)°C#';
        $regExGetTempWater = '#([0-9]+)#';

        $client = new Client();
        $prevTempWater = array();

        $day = new \DateTime("now");
        // attention dû à une erreure coté meteocity, il faut mettre 20151128 pour avoir les infos du 29/11/2015 ...
        $inter1Day=new \DateInterval('P01D');
        $inter1Day->invert=1;
        $day->add($inter1Day); // enlever un jour
        $inter1Day->invert=0;

        for ($numJour = 0; $numJour <= 5; $numJour++) {
            $ajaxUrl=str_replace("__date__",$day->format('Ymd'),$ajaxUrlType);
            $day->add($inter1Day); // ajout d'un jour
            $output->writeln('<info>url : '.$ajaxUrl.'</info>');

            $crawler = $client->request('GET', $ajaxUrl);
            /*
            <div class="data-temp-eau">
                <div class="title"><h2>Température de l'eau</h2></div>
                <div class="data-number"><span class="icon"></span> 12°C</div>
            </div>
            */
            $divWater = $crawler->filter(".data-temp-eau"); // get <div class="data-temp-eau" ...>
            $divTempsWater = $divWater->filter(".data-number"); // and <div class="data-number" ...>

            preg_match($regExGetTempWater,$divTempsWater->text(),$tempWater);

            $prevTempWater[$numJour]=$tempWater[1];
            $output->writeln('<info>temperature de l eau pour le '.$day->format('d/m/Y').': '.$tempWater[1].'</info>');
        }

        return $prevTempWater;
    }

    /*
     * @meteocityURL: http://www.meteocity.com/france/plage/dieppe_p76217/
     * @return http://www.meteocity.com/ajax/Beach/ajaxChangeBeachView/?date=__date__+&citId=76217&modificateur=1
     */
    static function buildMeteocityAjaxURL($meteocityURL) {
        $regExGetCityId = '#_p([0-9]+)#i';
        preg_match($regExGetCityId,$meteocityURL,$cityId);
        return "http://www.meteocity.com/ajax/Beach/ajaxChangeBeachView/?date=__date__+&citId=".$cityId[1]."&modificateur=1";
    }

    /**
     * @param $prevTempWater: tableau du des T°C depuis aujourd'hui [0]=12 [1]=13 (issue de TempWaterGetData::getTempWater)
     *  Aujourd'hui: 12°C, demain : 13°C ...
     *
     */
    static function saveTempWater($spot, $prevTempWaterTab, $entityManager, $output) {
        $output->writeln('<info>****** function saveTempWater ****</info>');

        //TempWaterGetData::deleteOldTempWater($spot,$entityManager,$output); -> on n'efface pas les ancienne valeur
        $futureNotesDate= $entityManager->getRepository('LaPoizWindBundle:NotesDate')->getFutureNotesDate($spot);

        $currentDay=new \DateTime("now");
        foreach ($prevTempWaterTab as $jour => $prevTempWater) {
            // $jour=0 puis 1 ...
            $previsionNotesDate=$entityManager->getRepository('LaPoizWindBundle:NotesDate')->getNotesDateForDatePrev($spot,$currentDay);
            if ($previsionNotesDate != null) {
                $previsionNotesDate->setTempWater($prevTempWater);
                $entityManager->persist($previsionNotesDate);
            } else {
                // N'existe pas dans le BD -> on le créé => pas normal !!!!!
                $previsionNotesDate = new NotesDate();
                $previsionNotesDate->setTempWater($prevTempWater);
                $previsionNotesDate->setDatePrev(clone $currentDay);
                $previsionNotesDate->setSpot($spot);
                $spot->addNotesDate($previsionNotesDate);
                $entityManager->persist($previsionNotesDate);
                $entityManager->persist($spot);
            }
            $output->writeln('save tempWater:'.$previsionNotesDate->getTempWater().'°C for '.$previsionNotesDate->getDatePrev()->format('d/m/Y'));
            $currentDay= date_add($currentDay, new \DateInterval('P1D'));
        };
        $entityManager->flush();
    }

    /**
     * Attention - Efface toutes les T°C de la BD
     *
    static function deleteTempWater($spot, $entityManager, $output) {
        $output->writeln('<info>****** function deleteTempWater ****</info>');

        $list=$spot->getListPrevisionTempWater();
        $output->writeln('<info>delete all T°C water for the spot : '.$spot->getNom().'</info>');
        try {
            $spot->removeListPrevisionTempWater($list);
            $entityManager->persist($spot);
            $entityManager->flush();
        } catch (\Exception $ex) {
            $output->writeln("Exception Found - " . $ex->getMessage());;
        }
    }

    /**
     * Efface les anciennes prévisions
     *
    static function deleteOldTempWater($spot,$entityManager,$output) {
        $oldPrevisionTempWater = $entityManager->getRepository('LaPoizWindBundle:PrevisionTempWater')->getOldPrevisionTempWater($spot);
        foreach ($oldPrevisionTempWater as $previsionTempWater) {
            try {
                $entityManager->remove($previsionTempWater);
                $entityManager->flush();
            } catch (\Exception $ex) {
                $output->writeln("Exception Found - " . $ex->getMessage());;
            }
        }
    }
    */
} 