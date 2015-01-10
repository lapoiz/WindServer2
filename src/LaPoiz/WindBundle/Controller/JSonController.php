<?php

namespace LaPoiz\WindBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use LaPoiz\WindBundle\Controller\FOJsonController;

use LaPoiz\WindBundle\core\graph\TransformeToHighchartsDataTabForJson;

class JSonController extends Controller {


    /**
     * @Template()
     */
    public function listSpotsAction()
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $spots = $em->getRepository('LaPoizWindBundle:Spot')->findAll();

        $listSpot= array();
        foreach ($spots as $key => $spot) {
            $data= array('name' => $spot->getNom(), "id" => $spot->getId());
            $listSpot[]=$data;
        }
        return new JsonResponse($listSpot);

    }

    /**
     * @Template()
     */
    public function detailSpotAction($id=null, $renderTo=null)
    {
        //return $this->forward('FOJsonController:getAction', array('id' => $id));
        return $this->redirect($this->generateUrl('_fo_json_spot_data', array('id' => $id)));
    }

}