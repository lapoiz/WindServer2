<?php
namespace LaPoiz\WindBundle\Controller;

use LaPoiz\WindBundle\Entity\DataWindPrev;
use LaPoiz\WindBundle\Entity\WebSite;
use LaPoiz\WindBundle\Form\SpotType;
use LaPoiz\WindBundle\Form\DataWindPrevType;
use LaPoiz\WindBundle\core\maree\MareeGetData;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Validator\Constraints\Url;

class BOAjaxSiteController extends Controller

{


    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/1/addSite
     */
    public function spotAddSiteAction($id=null, Request $request)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        if (isset($id) && $id!=-1)
        {
            $spot = $em->find('LaPoizWindBundle:Spot', $id);
            if (!$spot)
            {
                return $this->container->get('templating')->renderResponse(
                    'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                    array('errMessage' => "No spot find !"));
            }
            $dataWindPrev = new DataWindPrev();
            $dataWindPrev->setSpot($spot);
            $form = $this->createForm('dataWindPrevForm',$dataWindPrev)
            ->add('add to spot','submit');

            if ('POST' == $request->getMethod()) {
                //$form->submit($request);
                $form->handleRequest($request);
                //$form->submit($request->request->get($form->getName()));

                if ($form->isValid()) {
                    // form submit
                    $dataWindPrev = $form->getData();
                    $spot->addDataWindPrev($dataWindPrev);
                    $site = $dataWindPrev->getWebsite();
                    $site->addDataWindPrev($dataWindPrev);

                    $em->persist($dataWindPrev);
                    $em->persist($site);
                    $em->persist($spot);
                    $em->flush();

                    return $this->forward('LaPoizWindBundle:BO:editSpot', array(
                            'id'  => $spot->getId()
                        ));
                    /*
                    return $this->render('LaPoizWindBundle:BackOffice/Spot/Ajax:addSite.html.twig', array(
                            'spot' => $spot,
                            'form' => $form->createView(),
                            'create' => true
                        )
                    );*/
                }
                /*else {
                    return new Response($request);
                }*/
            }

            return $this->render('LaPoizWindBundle:BackOffice/Spot/Ajax:addSite.html.twig', array(
                    'spot' => $spot,
                    'form' => $form->createView(),
                    'create' => false
                )
            );
        } else {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                array('errMessage' => "Miss id of spot... !"));
        }
    }

    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/webSite/1
     */
    public function spotWebSiteAction($id=null)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        if (isset($id) && $id!=-1)
        {
            $dataWindPrev = $em->find('LaPoizWindBundle:DataWindPrev', $id);
            if (!$dataWindPrev)
            {
                return $this->container->get('templating')->renderResponse(
                    'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                    array('errMessage' => "DataWindPrev not find !"));
            }

            return $this->render('LaPoizWindBundle:BackOffice/Spot/Ajax:webSite.html.twig', array(
                    'dataWindPrev' => $dataWindPrev
                )
            );
        } else {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                array('errMessage' => "Miss id of dataWindPrev... !"));
        }
    }

    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/site/delete/1
     */
    public function spotSiteDeleteAction($id=null)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        if (isset($id) && $id!=-1)
        {
            $dataWindPrev = $em->find('LaPoizWindBundle:DataWindPrev', $id);
            if (!$dataWindPrev)
            {
                return $this->container->get('templating')->renderResponse(
                    'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                    array('errMessage' => "DataWindPrev not find !"));
            }

            $idSpot = $dataWindPrev->getSpot()->getId();

            $em->remove($dataWindPrev);
            $em->flush();

            return $this->forward('LaPoizWindBundle:BO:displaySpot', array(
                    'id'  => $idSpot
                ));

        } else {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                array('errMessage' => "Miss id of dataWindPrev... !"));
        }
    }

    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/site/edit/1
     */
    public function spotSiteEditAction($id=null, Request $request)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        if (isset($id) && $id!=-1)
        {
            $dataWindPrev = $em->find('LaPoizWindBundle:DataWindPrev', $id);
            if (!$dataWindPrev)
            {
                return $this->container->get('templating')->renderResponse(
                    'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                    array('errMessage' => "DataWindPrev not find !"));
            }
            $form = $this->createForm('dataWindPrevForm',$dataWindPrev)
                ->add('save','submit');

            if ('POST' == $request->getMethod()) {
                $form->handleRequest($request);

                if ($form->isValid()) {
                    // form submit
                    $dataWindPrev = $form->getData();
                    $em->persist($dataWindPrev);
                    $em->flush();
                }
            }

            return $this->render('LaPoizWindBundle:BackOffice/Spot/Ajax:editSite.html.twig', array(
                    'dataWindPrev' => $dataWindPrev,
                    'form' => $form->createView()
                )
            );

        } else {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                array('errMessage' => "Miss id of dataWindPrev... !"));
        }
    }
}