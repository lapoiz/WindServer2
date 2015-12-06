<?php
namespace LaPoiz\WindBundle\Controller;

use LaPoiz\WindBundle\Entity\Spot;
use LaPoiz\WindBundle\Entity\Region;
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
    $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAllValid();
    $listRegion = $em->getRepository('LaPoizWindBundle:Region')->findAllOrderByNumDisplay();
    $listSpotNotValid = $em->getRepository('LaPoizWindBundle:Spot')->findAllNotValid();


    return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice:index.html.twig',
        array(
            'listSpot' => $listSpot,
            'listRegion' => $listRegion,
            'listSpotNotValid' => $listSpotNotValid
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
        $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAllValid();
        $listRegion = $em->getRepository('LaPoizWindBundle:Region')->findAllOrderByNumDisplay();

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
                    'listSpot' => $listSpot,
                    'listRegion' => $listRegion
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
        $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAllValid();
        $listRegion = $em->getRepository('LaPoizWindBundle:Region')->findAllOrderByNumDisplay();

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
                        'listSpot' => $listSpot,
                        'listRegion' => $listRegion
                    ));
            } else {
                return $this->render('LaPoizWindBundle:BackOffice/Spot:createSpot.html.twig', array(
                        'form' => $form->createView(),
                        'listSpot' => $listSpot,
                        'listRegion' => $listRegion
                    ));
            }
        }
        return $this->render('LaPoizWindBundle:BackOffice/Spot:createSpot.html.twig', array(
                'form' => $form->createView(),
                'listSpot' => $listSpot,
                'listRegion' => $listRegion
            ));
    }


    /*
    public function editRegionAction($id=null)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        // récupere tous les spots
        $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAllValid();
        $listRegion = $em->getRepository('LaPoizWindBundle:Region')->findAll();
        $listSpotWitoutRegion = $em->getRepository('LaPoizWindBundle:Spot')->findAllWithoutRegion();

        if (isset($id) && $id!=-1)
        {
            $region = $em->find('LaPoizWindBundle:Region', $id);
            if (!$region)
            {
                return $this->container->get('templating')->renderResponse(
                    'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                    array('errMessage' => "No region find !"));
            }
            return $this->render('LaPoizWindBundle:BackOffice:region.html.twig', array(
                'region' => $region,
                'listSpot' => $listSpot,
                'listRegion' => $listRegion,
                'listSpotWitoutRegion' => $listSpotWitoutRegion
            ));
        } else {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                array('errMessage' => "Miss id of region... !"));
        }
    }
*/
    /**
     * @Template()
     *     /admin/region/edit/
     */
    public function editRegionAction($id=null, Request $request)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        // récupere tous les spots, pour l'affichage de la page (barre de nav)
        $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAllValid();
        $listRegion = $em->getRepository('LaPoizWindBundle:Region')->findAllOrderByNumDisplay();
        $listSpotWitoutRegion = $em->getRepository('LaPoizWindBundle:Spot')->findAllWithoutRegion();

        if (isset($id) && $id!=-1) {
            $region = $em->find('LaPoizWindBundle:Region', $id);
            if (!$region) {
                return $this->container->get('templating')->renderResponse(
                    'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                    array('errMessage' => "No region find !"));
            }
        } else {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                array('errMessage' => "No id of region find !"));
        }

        $form = $this->createForm('region',$region);

        $form->add('actions', 'form_actions', [
            'buttons' => [
                'save' => ['type' => 'submit', 'options' => ['label' => 'Save']],
            ]
        ]);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $region = $form->getData();
                $em->persist($region);
                $em->flush();
            }
        }
        return $this->render('LaPoizWindBundle:BackOffice:region.html.twig', array(
            'form' => $form->createView(),
            'region' => $region,
            'listSpot' => $listSpot,
            'listRegion' => $listRegion,
            'listSpotWitoutRegion' => $listSpotWitoutRegion
        ));
    }



    /**
     * @Template()
     */
    public function createRegionAction(Request $request)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        // récupere tous les spots, pour l'affichage de la page (barre de nav)
        $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAllValid();
        $listRegion = $em->getRepository('LaPoizWindBundle:Region')->findAllOrderByNumDisplay();
        $listSpotWitoutRegion = $em->getRepository('LaPoizWindBundle:Spot')->findAllWithoutRegion();

        $region = new Region();
        $form = $this->createForm('region',$region);

        $form->add('actions', 'form_actions', [
            'buttons' => [
                'save' => ['type' => 'submit', 'options' => ['label' => 'Save']],
                'delete' => ['type' => 'submit', 'options' => ['label' => 'Delete']],
            ]
        ]);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $region = $form->getData();
                $em->persist($region);
                $em->flush();
                return $this->render('LaPoizWindBundle:BackOffice:region.html.twig', array(
                    'form' => $form->createView(),
                    'region' => $region,
                    'listSpot' => $listSpot,
                    'listRegion' => $listRegion,
                    'listSpotWitoutRegion' => $listSpotWitoutRegion
                ));
            } else {
                return $this->render('LaPoizWindBundle:BackOffice/Region:createRegion.html.twig', array(
                    'form' => $form->createView(),
                    'listSpot' => $listSpot,
                    'listRegion' => $listRegion,
                    'listSpotWitoutRegion' => $listSpotWitoutRegion
                ));
            }
        }
        return $this->render('LaPoizWindBundle:BackOffice/Region:createRegion.html.twig', array(
            'form' => $form->createView(),
            'listSpot' => $listSpot,
            'listRegion' => $listRegion,
            'listSpotWitoutRegion' => $listSpotWitoutRegion
        ));
    }


    /**
     * @Template()
     */
    public function deleteRegionAction($id=null) {
        if (isset($id) && $id!=-1)
        {
            $em = $this->container->get('doctrine.orm.entity_manager');
            $region = $em->find('LaPoizWindBundle:Region', $id);
            if (!$region) {
                return $this->container->get('templating')->renderResponse(
                    'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                    array('errMessage' => "No region find !"));
            }

            foreach ($region->getSpots() as $spot) {
                $spot->setRegion(null);
                $em->persist($spot);
            }

            $em->remove($region);
            $em->flush();
            return $this->indexAction();
        } else {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                array('errMessage' => "Miss id of region... !"));
        }
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
        $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAllValid();
        $listRegion = $em->getRepository('LaPoizWindBundle:Region')->findAllOrderByNumDisplay();

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
                    'listSpot' => $listSpot,
                    'listRegion' => $listRegion)
            );
        } else {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                array('errMessage' => "Miss id of dataWindPrev... !"));
        }
    }



}