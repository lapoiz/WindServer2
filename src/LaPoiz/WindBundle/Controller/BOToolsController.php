<?php
namespace LaPoiz\WindBundle\Controller;

use LaPoiz\WindBundle\core\imagesManage\RosaceWindManage;
use LaPoiz\WindBundle\Entity\Spot;
use LaPoiz\WindBundle\Entity\Region;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class BOToolsController extends Controller

{
  /**
   * @Template()
   *
   */
  public function rosaceConvertPNGAction()
  {
    $em = $this->container->get('doctrine.orm.entity_manager');

    $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAllValid();
    $listRegion = $em->getRepository('LaPoizWindBundle:Region')->findAllOrderByNumDisplay();
    $listSpotNotValid = $em->getRepository('LaPoizWindBundle:Spot')->findAllNotValid();
    $listSpotsWithoutRegion = $em->getRepository('LaPoizWindBundle:Spot')->findAllWithoutRegion();
    $listWebsites = $em->getRepository('LaPoizWindBundle:WebSite')->findAll();

    $spot=$em->getRepository('LaPoizWindBundle:Spot')->findFirst();
    RosaceWindManage::createRosaceWind($spot, $this);

    return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice/Tools:rosaceConvertPNG.html.twig',
        array(
            'spot' => $spot,
            'listSpot' => $listSpot,
            'listRegion' => $listRegion,
            'listSpotNotValid' => $listSpotNotValid,
            'listSpotsWithoutRegion' => $listSpotsWithoutRegion,
            'listWebsites' => $listWebsites
        ));
  }

  /**
   * @Template()
   *
   */
  public function rosaceConvertPNGAllSpotsAction()
  {
    $em = $this->container->get('doctrine.orm.entity_manager');

    $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAllValid();
    $listRegion = $em->getRepository('LaPoizWindBundle:Region')->findAllOrderByNumDisplay();
    $listSpotNotValid = $em->getRepository('LaPoizWindBundle:Spot')->findAllNotValid();
    $listSpotsWithoutRegion = $em->getRepository('LaPoizWindBundle:Spot')->findAllWithoutRegion();
    $listWebsites = $em->getRepository('LaPoizWindBundle:WebSite')->findAll();

    foreach ($listSpot as $spot) {
      RosaceWindManage::createRosaceWind($spot, $this);
    }
    foreach ($listSpotNotValid as $spot) {
      RosaceWindManage::createRosaceWind($spot, $this);
    }

    return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice/Tools:rosaceConvertPNGAllSpots.html.twig',
        array(
            'listSpot' => $listSpot,
            'listRegion' => $listRegion,
            'listSpotNotValid' => $listSpotNotValid,
            'listSpotsWithoutRegion' => $listSpotsWithoutRegion,
            'listWebsites' => $listWebsites
        ));
  }

}