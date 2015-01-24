<?php
namespace LaPoiz\WindBundle\Controller;

use LaPoiz\WindBundle\Entity\Spot;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;

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
    public function editSpotAction($id=null)
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
    public function createSpotAction(Request $request)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        // récupere tous les spots
        $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAll();

        $spot = new Spot();
        $form = $this->createForm('spot',$spot);

        $form->add('actions', 'form_actions', [
                'buttons' => [
                    'save' => ['type' => 'submit', 'options' => ['label' => 'Save']],
                ]
            ]);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $spot = $form->getData();
                $em->persist($spot);
                $em->flush();
                return $this->render('LaPoizWindBundle:BackOffice:spot.html.twig', array(
                        'spot' => $spot,
                        'listSpot' => $listSpot
                    ));
            } else {
                return $this->render('LaPoizWindBundle:BackOffice/Spot:createSpot.html.twig', array(
                        'form' => $form->createView(),
                        'listSpot' => $listSpot
                    ));
            }
        }
        return $this->render('LaPoizWindBundle:BackOffice/Spot:createSpot.html.twig', array(
                'form' => $form->createView(),
                'listSpot' => $listSpot
            ));
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