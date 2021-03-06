<?php

namespace LaPoiz\TableBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class WindTableController extends Controller
{
    public function spotAction($id=null) {

        $em = $this->container->get('doctrine.orm.entity_manager');

        if (isset($id) && $id!=-1) {
            $spot = $em->find('LaPoizWindBundle:Spot', $id);
            if (!$spot) {
                return $this->container->get('templating')->renderResponse(
                    'LaPoizWindBundle:FrontOffice:errorPage.html.twig',
                    array('errMessage' => "No spot find !"));
            }
        }  else {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                array('errMessage' => "Miss id of spot... !"));
        }

        return $this->render('LaPoizTableBundle:Wind:spot.html.twig', array('spot' => $spot));
    }
}
