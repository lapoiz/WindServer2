<?php
namespace LaPoiz\WindBundle\core\infoSpot;

use LaPoiz\WindBundle\Entity\Spot;
use LaPoiz\WindBundle\Entity\InfoSpot;

use \Doctrine\ORM\EntityManager;
use LaPoiz\WindBundle\Form\InfoSpotType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ManageInfoSpot
{
    public static function saveNewInfoSpot(Spot $spot,InfoSpot $infoSpot,EntityManager $em) {
        $spot->addInfoSpot($infoSpot);
        $infoSpot->setSpot($spot);
        $em->persist($infoSpot);
        $em->persist($spot);
        $em->flush();
    }

    public static function saveInfoSpot(InfoSpot $infoSpot,EntityManager $em) {
        $em->persist($infoSpot);
        $em->flush();
    }

    public static function deleteInfoSpot(InfoSpot $infoSpot,EntityManager $em) {
        $spot=$infoSpot->getSpot();
        $spot->removeInfoSpot($infoSpot);
        $em->persist($spot);
        $em->remove($infoSpot);
        $em->flush();
    }

    public static function createEditForm(Controller $controller, InfoSpot $infoSpot) {
        $form = $controller->createForm(InfoSpotType::class,$infoSpot, array(
            'action' => $controller->generateUrl('_bo_ajax_spot_edit_spot_info', array('id' => $infoSpot->getId())),
            'method' => 'POST',));
        $form->add('save',SubmitType::class, array(
            'label' => 'Save',
            'attr'  => array('class' => 'btn btn-default pull-right')));
        return $form;

    }
    public static function createNewForm(Controller $controller, Spot $spot) {
        $infoSpot=new InfoSpot();
        $form = $controller->createForm(InfoSpotType::class,$infoSpot, array(
            'action' => $controller->generateUrl('_bo_ajax_spot_add_spot_info', array('id' => $spot->getId())),
            'method' => 'POST',));
        $form->add('Create',SubmitType::class, array(
            'label' => 'Create',
            'attr'  => array('class' => 'btn btn-default pull-right')));
        return $form;
    }

    public static function getFirstInfoSpot(Spot $spot) {
        if ($spot->getInfoSpot()!== null && count($spot->getInfoSpot())>0) {
            return $spot->getInfoSpot()->first();
        } else {
            return null;
        }
    }
}