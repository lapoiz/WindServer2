<?php
namespace LaPoiz\WindBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class BOController extends Controller

{
  /**
   * @Template()
   *
   * Home page of BO
   */
  public function indexAction()
  {
    $em = $this->container->get('doctrine.orm.entity_manager');
    // récupere tous les spots
    $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAll();

    return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice:index.html.twig',
        array(
            'listSpot' => $listSpot
        ));
  }

    /**
     * @Template()
     *
     * In BO when click on a spot
     * if no dataWindPrev in spot -> here
     * else -> dataWindPrevAction
     */
    public function displaySpotAction($id=null)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        // récupere tous les spots
        $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAll();


        if (isset($id) && $id!=-1)
        {
            $spot = $em->find('LaPoizWindBundle:Spot', $id);
            if (!$spot)
            {
                return $this->container->get('templating')->renderResponse(
                    'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                    array('errMessage' => "No spot find !"));
            }
            return $this->render('LaPoizWindBundle:BackOffice:spot.html.twig', array(
                    'spot' => $spot,
                    'listSpot' => $listSpot
                ));
        } else {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                array('errMessage' => "Miss id of spot... !"));
        }

    }

    /**
     * @Template()
     */
    public function deleteSpotAction($id)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $spot = $em->find('LaPoizWindBundle:Spot', $id);
        if (!$spot)
        {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:BackOffice:errorBlock.html.twig',
                array('errMessage' => "No spot find !"));
        }
        // spot find
        $spotId = $spot->getId();
        $em->remove($spot);
        $em->flush();

        // display BO home page
        $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAll();
        $notification = array ('type'=>'success',
            'title'=>$this->get('translator')->trans('notification.info.spot.delete.title'),
            'content'=>$this->get('translator')->trans('notification.info.spot.delete.content'));


        return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice:index.html.twig',
            array(
                'listSpot' => $$listSpot,
                'notification' => $notification
            )
        );
    }




    /**
     * @Template()
     *
     * In BO when click on a spot/webSite
     */
    public function dataWindPrevAction($id=null)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        // récupere tous les spots
        $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAll();

        if (isset($id) && $id!=-1)
        {
            $dataWindPrev = $em->find('LaPoizWindBundle:DataWindPrev', $id);
            if (!$dataWindPrev)
            {
                return $this->container->get('templating')->renderResponse(
                    'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                    array('errMessage' => "No spot/WebSite find !"));
            }
            $spot = $dataWindPrev->getSpot();
            return $this->render('LaPoizWindBundle:BackOffice/Spot:dataWindPrev.html.twig', array(
                    'dataWindPrev' => $dataWindPrev,
                    'spot' => $spot,
                    'listSpot' => $listSpot)
            );
        } else {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                array('errMessage' => "Miss id of dataWindPrev... !"));
        }
    }



}