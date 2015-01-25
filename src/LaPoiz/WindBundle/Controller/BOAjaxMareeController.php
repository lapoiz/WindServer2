<?php
namespace LaPoiz\WindBundle\Controller;

use LaPoiz\WindBundle\Entity\DataWindPrev;
use LaPoiz\WindBundle\Entity\WebSite;
use LaPoiz\WindBundle\Form\MareeType;
use LaPoiz\WindBundle\Form\SpotType;
use LaPoiz\WindBundle\Form\DataWindPrevType;
use LaPoiz\WindBundle\core\maree\MareeGetData;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Validator\Constraints\Url;

class BOAjaxMareeController extends Controller

{

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
            $prevMaree=MareeGetData::getMaree($spot->getMareeURL());

            return $this->render('LaPoizWindBundle:BackOffice/Spot/Ajax/Maree:prevMaree.html.twig', array(
                    'prevMaree' => $prevMaree,
                    'mareeURL' => $spot->getMareeURL(),
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
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/2/maree/create
     */
    public function mareeCreateAction($id=null, Request $request)
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
/*
            if ($spot->getMareeURL()==null) {
                $spot->setMareeURL(new Url());
            }
*/
            //$defaultData = array('message' => 'Type your message here');
            $form = $this->createFormBuilder(['attr' => ['id' => 'maree_form']])
                ->add('URL', 'url',
                    array('label' => "URL (du type: http://maree.info/X): "))
                ->add('Add','submit')
                ->getForm();

            if ('POST' == $request->getMethod()) {
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $URL = $form->getData()['URL'];
                    $spot->setMareeURL($URL);

                    $em->persist($spot);
                    $em->flush();

                    return $this->forward('LaPoizWindBundle:BO:editSpot', array(
                            'id'  => $spot->getId()
                        ));
                }
            }

            return $this->render('LaPoizWindBundle:BackOffice/Spot/Ajax/Maree:mareeCreate.html.twig', array(
                    'form' => $form->createView(),
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

            $form = $this->createForm(new MareeType(), $spot)
                ->add('Save','submit');

            if ($request->isMethod('POST')) {
                // envoie du formulaire pour modification des données marées
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $spot = $form->getData();
                    $mareeRestrictions = $spot->getMareeRestriction();
                    foreach ($mareeRestrictions as $restriction) {
                        $restriction->setSpot($spot);
                        $em->persist($restriction);
                    }
                    $em->persist($spot);
                    $em->flush();
                }
            }

            return $this->render('LaPoizWindBundle:BackOffice/Spot/Ajax/Maree:mareeEdit.html.twig', array(
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

        $mareeURL = $spot->getMareeURL();
        if (!empty($mareeURL)) {
            $prevMaree = MareeGetData::getMaree($mareeURL);
            MareeGetData::saveMaree($spot,$prevMaree,$em,new NullOutput());
        }

        $mareeDateDB = $em->getRepository('LaPoizWindBundle:MareeDate')->findLastPrev(10, $spot);

        return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice/Spot/Ajax/Maree:mareeSaveResult.html.twig',
            array(
                'mareeDateDB' => $mareeDateDB,
                'message' => "",
                'saveSuccess' => true
            ));
    }

    /**
     * @Template()
     *

     */
    public function spotMareeDeleteAction($id=null)
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

            $spot->setMareeURL(null);
            $em->persist($spot);
            $em->flush();

            return $this->forward('LaPoizWindBundle:BO:editSpot', array(
                    'id'  => $spot->getId()
                ));

        } else {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                array('errMessage' => "Miss id of dataWindPrev... !"));
        }
    }


}