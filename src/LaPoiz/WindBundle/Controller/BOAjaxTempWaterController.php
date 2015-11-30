<?php
namespace LaPoiz\WindBundle\Controller;

use LaPoiz\WindBundle\Form\TempWaterType;
use LaPoiz\WindBundle\core\tempWater\TempWaterGetData;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Validator\Constraints\Url;

class BOAjaxTempWaterController extends Controller

{
    /**
     * @Template()
     *
     * http://localhost/WindServer/web/app_dev.php/admin/BO/ajax/tempwater/get/1
     */
    public function getTempWaterPrevAction($id=null)
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
            $prevTempWater=TempWaterGetData::getTempWater($spot->getTempWaterURL(),new NullOutput());

            return $this->render('LaPoizWindBundle:BackOffice/Spot/Ajax/TempWater:prevTempWater.html.twig', array(
                    'prevTempWater' => $prevTempWater,
                    'tempWaterURL' => $spot->getTempWaterURL(),
                    'spot' => $spot
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
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/2/tempwater/create
     */
    public function tempWaterCreateAction($id=null, Request $request)
    {
        return $this->spotTempWaterEditAction($id,$request);
    }

    /**
     * @Template()
     *
     * http://localhost/WindServer/web/app_dev.php/admin/BO/ajax/spot/1/tempwater/edit
     */
    public function spotTempWaterEditAction($id=null, Request $request)
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

            $form = $this->createForm(new TempWaterType(), $spot)
                ->add('Save','submit');

            if ($request->isMethod('POST')) {
                // envoie du formulaire pour modification des données de T°C
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $spot = $form->getData();
                    $em->persist($spot);
                    $em->flush();
                }
            }

            return $this->render('LaPoizWindBundle:BothOffice/Spot/Ajax/TempWater:tempWaterEdit.html.twig', array(
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
     * Sauvegarde les prévisions de T°C de l'eau en prenant en compte ce qui existe déjà dans la BD
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/tempwater/save/1
     */
    public function tempWaterSaveAction($id)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $spot = $em->find('LaPoizWindBundle:Spot', $id);

        if (!$spot)
        {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:Default:errorBlock.html.twig',
                array('errMessage' => "Spot not find !"));
        }

        $tempWaterURL = $spot->getTempWaterURL();
        if (!empty($tempWaterURL)) {
            $prevTempWater = TempWaterGetData::getTempWater($tempWaterURL, new NullOutput());
            TempWaterGetData::saveTempWater($spot,$prevTempWater,$em,new NullOutput());
        }

        $previsionTempWaterList = $em->getRepository('LaPoizWindBundle:PrevisionTempWater')->getFuturePrevisionTempWater($spot);

        return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice/Spot/Ajax/TempWater:tempWaterSaveResult.html.twig',
            array(
                'previsionTempWaterList' => $previsionTempWaterList,
                'spot' => $spot,
                'message' => "",
                'saveSuccess' => true
            ));
    }

    /**
     * @Template()
     * Efface les prévisions de T°C de l'eau
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/tempwater/delete/1
     */
    public function tempWaterDeleteAction($id)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $spot = $em->find('LaPoizWindBundle:Spot', $id);

        if (!$spot)
        {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:Default:errorBlock.html.twig',
                array('errMessage' => "Spot not find !"));
        }

        $tempWaterURL = $spot->getTempWaterURL();
        if (!empty($tempWaterURL)) {
            TempWaterGetData::deleteTempWater($spot,$em,new NullOutput());
        }

        $previsionDate = $em->getRepository('LaPoizWindBundle:PrevisionDate')->findLastPrev(10, $spot);

        return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice/Spot/Ajax/TempWater:tempWaterSaveResult.html.twig',
            array(
                'previsionDateDB' => $previsionDateDB,
                'spot' => $spot,
                'message' => "",
                'saveSuccess' => true
            ));
    }

    /**
     * @Template()
     *
     */
    public function spotTempWaterDeleteAction($id=null)
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

            $spot->setTempWaterURL(null);
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

    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/maree/{idURLInfoMaree}/forDay/{idDateURLInfoMaree}
     * dateMaree = 20150106
     */
/*    public function getMareeForDayAction($idURLInfoMaree=null,$idDateURLInfoMaree=null, Request $request)
    {
        // Récupere la page: http://maree.info/$idURLInfoMaree?d=$idDateURLInfoMaree
        // Parse avec ce qui est déjà fait dans core -> MareeGetData
        // envoie la hauteur marée haute et marée basse en JSON
        return new JsonResponse(MareeGetData::getHauteurMaree($idURLInfoMaree, $idDateURLInfoMaree));
    }
*/
}