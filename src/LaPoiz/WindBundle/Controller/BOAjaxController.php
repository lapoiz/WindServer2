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

class BOAjaxController extends Controller

{

    /**
     * @Template()
     *
     * http://localhost/WindServer/web/app_dev.php/admin/BO/ajax/spot/display/1
     */
    public function spotDisplayAction($id=null)
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
            $form = $this->createForm('spot',$spot, array('read_only' => true));

            return $this->render('LaPoizWindBundle:BackOffice/Spot/Ajax:spotDisplay.html.twig', array(
                    'spot' => $spot,
                    'form' => $form->createView()
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
     * http://localhost/WindServer/web/app_dev.php/admin/BO/ajax/spot/edit/1
     */
    public function spotEditAction($id=null, Request $request)
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
            $form = $this->createForm('spot',$spot);

            $form->add('actions', 'form_actions', [
                'buttons' => [
                    'save' => ['type' => 'submit', 'options' => ['label' => 'Save']],
                ]
            ]);

            if ('POST' == $request->getMethod()) {
                //$form->submit($request);
                $form->handleRequest($request);
                //$form->submit($request->request->get($form->getName()));

                if ($form->isValid()) {
                    // form submit
                    $spot = $form->getData();
                    $em->persist($spot);
                    $em->flush();
                }
                /*else {
                    return new Response($request);
                }*/
            }

            return $this->render('LaPoizWindBundle:BackOffice/Spot/Ajax:spotEdit.html.twig', array(
                    'spot' => $spot,
                    'form' => $form->createView()
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
     * http://localhost/WindServer/web/app_dev.php/admin/BO/ajax/spot/delete/1
     */
    public function spotDeleteAction($id=null)
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
            $em->remove($spot);
            $em->flush();

            $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAll();

            return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice:index.html.twig',
                array(
                    'listSpot' => $listSpot
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
     * http://localhost/WindServer/web/app_dev.php/admin/BO/ajax/spot/1/addSite
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
            $form = $this->createForm('dataWindPrevForm',$dataWindPrev);

            $form->add('actions', 'form_actions', [
                    'buttons' => [
                        'add' => ['type' => 'submit', 'options' => ['label' => 'Add to spot']],
                    ]
                ]);

            if ('POST' == $request->getMethod()) {
                //$form->submit($request);
                $form->handleRequest($request);
                //$form->submit($request->request->get($form->getName()));

                if ($form->isValid()) {
                    // form submit
                    $dataWindPrev = $form->getData();
                    $spot->addDataWindPrev($dataWindPrev);
                    $site = $dataWindPrev->getWebsite;
                    $site->addDataWindPrev($dataWindPrev);

                    $em->persist($dataWindPrev);
                    $em->persist($site);
                    $em->persist($spot);
                    $em->flush();

                    return $this->render('LaPoizWindBundle:BackOffice/Spot/Ajax:addSite.html.twig', array(
                            'create' => true
                        )
                    );
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
     * http://localhost/WindServer/web/app_dev.php/admin/BO/ajax/spot/webSite/1
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
     * http://localhost/WindServer/web/app_dev.php/admin/BO/ajax/maree/get/1
     */
    public function getMareePrevAction($id=null)
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
            $prevMaree=MareeGetData::getMaree($spot->getParameter()->getMareeURL());

            return $this->render('LaPoizWindBundle:BackOffice/Spot/Ajax:prevMaree.html.twig', array(
                    'prevMaree' => $prevMaree,
                    'mareeURL' => $spot->getParameter()->getMareeURL(),
                    'spot' => $spot
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
     * http://localhost/WindServer/web/app_dev.php/admin/BO/ajax/spot/1/maree/edit
     */
    public function spotMareeEditAction($id=null, Request $request)
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
            $defaultData = array('message' => 'Type your message here');
            $form = $this->createFormBuilder($defaultData,['attr' => ['id' => 'maree_form']])
                ->add('URL', 'url',
                    array(
                        'label' => "URL (du type: http://maree.info/X): ",
                        'attr' => array('value' => $spot->getParameter()->getMareeURL())))

                ->add('actions', 'form_actions', [
                        'buttons' => [
                            'save' => ['type' => 'submit', 'options' => ['label' => 'Save']],
                        ]
                    ])
                ->getForm();


            if ('POST' == $request->getMethod()) {
                //$form->submit($request);
                $form->handleRequest($request);

                if ($form->isValid()) {
                    // form submit
                    $data = $form->getData();
                    $spot->getParameter()->setMareeURL($data["URL"]);
                    $em->persist($spot);
                    $em->flush();
                }
            }
            return $this->render('LaPoizWindBundle:BackOffice/Spot/Ajax:mareeEdit.html.twig', array(
                    'form' => $form->createView(),
                    'spot' => $spot
                ));

        } else {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                array('errMessage' => "Miss id of spot... !"));
        }
    }



    /**
     * @Template()
     * Sauvegarde les prévisions de marée en prenant en compte ce qui existe déjà dans la BD
     * http://localhost/WindServer/web/app_dev.php/admin/BO/ajax/maree/save/1
     */
    public function mareeSaveAction($id)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $spot = $em->find('LaPoizWindBundle:Spot', $id);

        if (!$spot)
        {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:Default:errorBlock.html.twig',
                array('errMessage' => "Spot not find !"));
        }

        $mareeURL = $spot->getParameter()->getMareeURL();
        if (!empty($mareeURL)) {
            $prevMaree = MareeGetData::getMaree($mareeURL);
            MareeGetData::saveMaree($spot,$prevMaree,$em,new NullOutput());
        }

        $mareeDateDB = $em->getRepository('LaPoizWindBundle:MareeDate')->findLastPrev(10);

        return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice/Spot/Ajax:mareeSaveResult.html.twig',
            array(
                'mareeDateDB' => $mareeDateDB,
                'message' => "",
                'saveSuccess' => true
            ));
    }
}