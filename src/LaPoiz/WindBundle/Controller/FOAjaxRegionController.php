<?php
namespace LaPoiz\WindBundle\Controller;

use LaPoiz\WindBundle\Entity\Spot;
use LaPoiz\WindBundle\Entity\Region;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Validator\Constraints\Url;

class FOAjaxRegionController extends Controller {

    /**
     * @Template()
     * Affiche le tableau (<Table>) des spots de la region avec toutes les infos importantes (nb heur de nav, température ...)
     * http://localhost/Wind/web/app_dev.php/fo/ajax/region/spots/infoNav/4
     */
    public function listSpotsInfoNavAction($id=null, Request $request) {
        $em = $this->container->get('doctrine.orm.entity_manager');

        if (!isset($id) && $id==-1 ) {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:FrontOffice:errorPage.html.twig',
                array('errMessage' => "No region find ! id:"));
        } else {
            $listSpots = null;
            if ($id==0) {
                // Spots non associés à une région
                $listSpots = $em->getRepository('LaPoizWindBundle:Spot')->findAllWithoutRegion();
            } else {
                $region = $em->find('LaPoizWindBundle:Region', $id);
                $listSpots=$region->getSpots();
            }

            $tabNotesSpots=array();

            // Construire le tableau, pour afficher les notes
            foreach ($listSpots as $spot) {
                $tabNotes = array();
                $listNotes=$spot->getNotesDate();

                $day= new \DateTime("now");
                for ($nbPrevision=0; $nbPrevision<7; $nbPrevision++) {
                    $tabNotes[$day->format('Y-m-d')]=null;
                    $day->modify('+1 day');
                }

                foreach ($listNotes as $notesDate) {
                    if (array_key_exists($notesDate->getDatePrev()->format('Y-m-d'), $tabNotes)) {
                        $tabNotes[$notesDate->getDatePrev()->format('Y-m-d')]=$notesDate;
                    }
                }

                $tabNotesSpots[$spot->getNom()]=$tabNotes;
            }

            return $this->container->get('templating')->renderResponse('LaPoizWindBundle:FrontOffice/Region:tableInfoNavSpots.html.twig',
                array(
                    'listSpots' => $listSpots,
                    'tabNotesSpots' => $tabNotesSpots
                ));
        }
    }
}