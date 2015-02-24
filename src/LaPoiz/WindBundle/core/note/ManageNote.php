<?php

namespace LaPoiz\WindBundle\core\note;


use LaPoiz\WindBundle\Entity\NotesDate;
use Symfony\Component\Validator\Constraints\DateTime;

class ManageNote {

    /**
     * @param $spot
     * Efface toutes les données plus vielle que today
     */
    static function deleteOldData($spot,$em) {

        // On check toutes les notesDate
        $listePrecedenteNotes = $spot->getNotesDate();

        if ($listePrecedenteNotes != null && count($listePrecedenteNotes)>0) {
            //la liste n'est pas vide
            $today=new \DateTime('now');
            $today->setTime(0, 0, 0);

            foreach ($listePrecedenteNotes as $precedenteNotes) {
                if ($precedenteNotes->getDatePrev() < $today) {
                    // avant today -> on efface
                    $em->remove($precedenteNotes);
                }
            }
            $em->flush();
        }

    }

    /**
     * @param $spot
     * @param $datePrev
     * @return renvoie la notesDate de spot à $datePrev, ou une nouvelle instance si elle n'existe pas
     */
    static function getNotesDate($spot, $datePrev, $em) {
        $datePrev->setTime(0, 0, 0);


        $noteDatesFind = null;
        $listeNotesDate = $spot->getNotesDate();
        foreach ($listeNotesDate as $notesDate) {
            if ($notesDate->getDatePrev() == $datePrev) {
                $noteDatesFind=$notesDate;
            }
        }

        if ($noteDatesFind==null) {
            $noteDatesFind=new NotesDate();
            $noteDatesFind->setSpot($spot);
            $noteDatesFind->setDatePrev($datePrev);
            $spot->addNotesDate($noteDatesFind);
            $em->persist($noteDatesFind);
            $em->persist($spot);
            $em->flush();
        }
        return $noteDatesFind;
    }

    /**
     * @param $spot
     * @param $datePrev avec heure=0 minute=0 sec=0
     * @return la NotesDate, null si elle n'existe pas
     */
    static function getNoteDateInSpot($spot, $datePrev) {
    }

} 