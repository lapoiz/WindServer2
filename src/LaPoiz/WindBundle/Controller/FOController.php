<?php
namespace LaPoiz\WindBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class FOController extends Controller

{
    /**
     * @Template()
     * Page d'accueil du site, avec la liste des sites et leurs notes
     */
    public function indexAction()
    {
       $em = $this->container->get('doctrine.orm.entity_manager');
        // récupere tous les spots
        $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAll();

        // Construire le tableau, pour afficher les notes


        return $this->container->get('templating')->renderResponse('LaPoizWindBundle:FrontOffice:index.html.twig',
            array(
                'listSpot' => $listSpot));
    }


    /**
     * @Template()
     */
  public function conceptAction()
  {
      $em = $this->container->get('doctrine.orm.entity_manager');
      // récupere tous les spots
      $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAll();

      return $this->container->get('templating')->renderResponse('LaPoizWindBundle:FrontOffice:concept.html.twig',
        array(
            'listSpot' => $listSpot));
  }

  /**
    * @Template()
  */
  public function spotGraphAction($id=null)
  {
    $em = $this->container->get('doctrine.orm.entity_manager');
    $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAll();

    if (isset($id) && $id!=-1)
    {
        $spot = $em->find('LaPoizWindBundle:Spot', $id);
        if (!$spot)
        {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:FrontOffice:errorPage.html.twig',
                array('errMessage' => "No spot find !"));
        }
        return $this->render('LaPoizWindBundle:FrontOffice:spot.html.twig', array(
                'spot' => $spot,
                'listSpot' => $listSpot
        ));
    }
  }
}