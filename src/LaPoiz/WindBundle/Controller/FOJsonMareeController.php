<?php

namespace LaPoiz\WindBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use LaPoiz\WindBundle\core\maree\MareeTools;

class FOJsonMareeController extends Controller {


    /**     *
     * http://localhost/Wind/web/app_dev.php/fo/json/lapoizgraph/plage/maree/spot/1
     */
    public function getPlageNavigationAction($id=null) {
        $em = $this->container->get('doctrine.orm.entity_manager');
        if (isset($id) && $id!=-1) {
            $spot = $em->find('LaPoizWindBundle:Spot', $id);
            if (!$spot) {
                return new JsonResponse(array(
                    'success' => false,
                    'description' => "No spot find with id:".$id
                ));
            }

            // Recupere les dernieres marées depuis aujourd'hui
            $listMaree=$em->getRepository('LaPoizWindBundle:MareeDate')->getFuturMaree($spot);
            $tabPlageRestriction=array();
            foreach ($listMaree as $mareeDate) {
                $tabPlageRestriction1Day = FOJsonMareeController::getPlageRestriction($spot, $mareeDate);
                $keyDate=$mareeDate->getDatePrev()->format('Y-m-d');
                $plageRestriction=array("date"=>$keyDate, "restrictions"=>$tabPlageRestriction1Day);
                $tabPlageRestriction[]=$plageRestriction;
            }
            return new JsonResponse(array(
                'success' => true,
                'description' => "Data find:",
                'data' => $tabPlageRestriction
            ));

        } else {
            return new JsonResponse(array(
                'success' => false,
                'description' => "No spot find with id:".$id
            ));
        }
    }



    /**
     * @param $spot
     * @param $mareeDate : prévision de marre que l'on va analyser
     * @return: Array: un tableau contenant la liste des plages horaires OK, KO et Warn
     */
    static function getPlageRestriction($spot, $mareeDate) {
        $mareeStateArray=array();
        $listePrevisionMaree=$mareeDate->getListPrevision();
        if ($listePrevisionMaree!=null && count($listePrevisionMaree)>=2) {

            // pour chaque $restriction de  $spot->getMareeRestriction()
            foreach ($spot->getMareeRestriction() as $mareeRestriction) {
                list($timeInState, $timeTab)=MareeTools::calculTimeInState($mareeRestriction, $listePrevisionMaree);
                $mareeStateArray[$mareeRestriction->getState()]=$timeTab;
            }
        }
        return $mareeStateArray;
    }
} 