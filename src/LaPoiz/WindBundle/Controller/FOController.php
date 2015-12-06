<?php
namespace LaPoiz\WindBundle\Controller;

use LaPoiz\WindBundle\Entity\Contact;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;

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
        $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAllValid();
        // récupere toutes les régions
        $listRegion = $em->getRepository('LaPoizWindBundle:Region')->findAllOrderByNumDisplay();
        $listSpotsWithoutRegion = $em->getRepository('LaPoizWindBundle:Spot')->findAllWithoutRegion();

        return $this->container->get('templating')->renderResponse('LaPoizWindBundle:FrontOffice:index.html.twig',
            array(
                'listSpot' => $listSpot,
                'listRegion' => $listRegion,
                'listSpotsWithoutRegion' => $listSpotsWithoutRegion
            ));
    }

    /**
     * @Template()
     */
  public function conceptAction()
  {
      $em = $this->container->get('doctrine.orm.entity_manager');
      // récupere tous les spots
      $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAllValid();

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
    $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAllValid();

    if (isset($id) && $id!=-1)
    {
        $spot = $em->find('LaPoizWindBundle:Spot', $id);
        if (!$spot)
        {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:FrontOffice:errorPage.html.twig',
                array('errMessage' => "No spot find !"));
        }
        // Here modif
        return $this->render('LaPoizWindBundle:FrontOffice:spot.html.twig', array(
                'spot' => $spot,
                'listSpot' => $listSpot
        ));
    }
  }

    /**
     * @Template()
     *
     * Page for ask a new spot
     */
    public function spotAskCreateAction(Request $request)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAllValid();

        return $this->render('LaPoizWindBundle:FrontOffice:askNewSpot.html.twig', array(
                    'listSpot' => $listSpot
                ));
    }

}