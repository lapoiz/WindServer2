<?php
namespace LaPoiz\WindBundle\Controller;

use LaPoiz\WindBundle\Command\CreateNbHoureCommand;
use LaPoiz\WindBundle\core\imagesManage\RosaceWindManage;
use LaPoiz\WindBundle\core\maree\MareeTools;
use LaPoiz\WindBundle\core\nbHoure\NbHoureMaree;
use LaPoiz\WindBundle\core\nbHoure\NbHoureMeteo;
use LaPoiz\WindBundle\core\nbHoure\NbHoureNav;
use LaPoiz\WindBundle\core\nbHoure\NbHoureWind;
use LaPoiz\WindBundle\core\note\ManageNote;
use LaPoiz\WindBundle\core\infoSpot\ManageInfoSpot;
use LaPoiz\WindBundle\Entity\DataWindPrev;
use LaPoiz\WindBundle\Entity\InfoSpot;
use LaPoiz\WindBundle\Entity\WebSite;
use LaPoiz\WindBundle\Form\SpotType;
use LaPoiz\WindBundle\Form\DataWindPrevType;
use LaPoiz\WindBundle\core\maree\MareeGetData;
use LaPoiz\WindBundle\Form\InfoSpotType;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Validator\Constraints\Url;

class BOAjaxSpotController extends Controller

{

    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/edit/1
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
            $form = $this->createForm('spot',$spot)
                ->add('save','submit')
                ->add('effacer','button',array(
                        'attr' => array(
                            'onclick' => 'effacerSpot()',
                            'class' => 'btn btn-danger'
                        ),
                    ));

            /*$form->add('actions', 'form_actions', [
                'buttons' => [
                    'save' => ['type' => 'submit', 'options' => ['label' => 'Save']],
                ]
            ]);*/

            if ('POST' == $request->getMethod()) {
                //$form->submit($request);
                $form->handleRequest($request);
                //$form->submit($request->request->get($form->getName()));

                if ($form->isValid()) {
                    // form submit
                    $spot = $form->getData();
                    $em->persist($spot);
                    $em->flush();
                    RosaceWindManage::createRosaceWind($spot, $this);
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
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/valid/1
     */
    public function spotValidAction($id=null)
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
            $spot->setIsValide(true);
            $em->persist($spot);
            $em->flush();

            $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAllValid();
            $listRegion = $em->getRepository('LaPoizWindBundle:Region')->findAllOrderByNumDisplay();
            $listSpotsWithoutRegion = $em->getRepository('LaPoizWindBundle:Spot')->findAllWithoutRegion();
            $listWebsites = $em->getRepository('LaPoizWindBundle:WebSite')->findAll();
            $listContacts = $em->getRepository('LaPoizWindBundle:Contact')->findAll();

            $listSpotNotValid = $em->getRepository('LaPoizWindBundle:Spot')->findAllNotValid();

            return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice:index.html.twig',
                array(
                    'listSpot' => $listSpot,
                    'listSpotNotValid' => $listSpotNotValid,
                    'listRegion' => $listRegion,
                    'listSpotsWithoutRegion' => $listSpotsWithoutRegion,
                    'listWebsites' => $listWebsites,
                    'listContacts' => $listContacts
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
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/delete/1
     *
     * Used for delete spot not valide
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

            $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAllValid();
            $listSpotNotValid = $em->getRepository('LaPoizWindBundle:Spot')->findAllNotValid();

            return $this->render('LaPoizWindBundle:BackOffice:index.html.twig',
                array(
                    'listSpot' => $listSpot,
                    'listSpotNotValid' => $listSpotNotValid,
                )
            );
        } else {
            return $this->render('LaPoizWindBundle:BackOffice:errorPage.html.twig',
                array('errMessage' => "Miss id of dataWindPrev... !"));
        }
    }


    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/nbHoureNav/1
     */
    public function calculNbHoureNavAction($id=null)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $spot = $em->find('LaPoizWindBundle:Spot', $id);

        if (!$spot) {
            return $this->render('LaPoizWindBundle:Default:errorBlock.html.twig',
                array('errMessage' => "Spot not find !"));
        }

        list($tabDataNbHoureNav,$tabDataMeteo)=NbHoureNav::createTabNbHoureNav($spot, $em);
        $tabNbHoureNav=NbHoureNav::calculateNbHourNav($tabDataNbHoureNav);
        $tabMeteo=NbHoureMeteo::calculateMeteoNav($tabDataMeteo);

        return $this->render('LaPoizWindBundle:BackOffice/Test:nbHoureNav.html.twig',
            array(
                'spot' => $spot,
                'tabNbHoure' => $tabNbHoureNav,
                'tabMeteo' => $tabMeteo,
                'message' => "",
                'saveSuccess' => true
            ));
    }

    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/save/nbHoureNav/1
     */
    public function saveNbHoureNavAction($id=null)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $spot = $em->find('LaPoizWindBundle:Spot', $id);

        if (!$spot) {
            return $this->render('LaPoizWindBundle:Default:errorBlock.html.twig',
                array('errMessage' => "Spot not find !"));
        }

        list($tabDataNbHoureNav,$tabDataMeteo)=NbHoureNav::createTabNbHoureNav($spot, $em);
        $tabNbHoureNav=NbHoureNav::calculateNbHourNav($tabDataNbHoureNav);
        $tabMeteo=NbHoureMeteo::calculateMeteoNav($tabDataMeteo);

        // Save nbHoure on spot
        foreach ($tabNbHoureNav as $keyDate=>$tabWebSite) {
            foreach ($tabWebSite as $keyWebSite=>$nbHoureNav) {
                $noteDates=ManageNote::getNotesDate($spot, \DateTime::createFromFormat('Y-m-d',$keyDate), $em);
                $nbHoureNavObj=ManageNote::getNbHoureNav($noteDates, $keyWebSite, $em);
                $nbHoureNavObj->setNbHoure($nbHoureNav);
                $em->persist($nbHoureNavObj);
                $em->persist($noteDates);
            }
        }

        // Save meteo
        foreach ($tabMeteo as $keyDate=>$tabMeteoDay) {
            $noteDates=ManageNote::getNotesDate($spot, \DateTime::createFromFormat('Y-m-d',$keyDate), $em);
            $noteDates->setTempMax($tabMeteoDay["tempMax"]);
            $noteDates->setTempMin($tabMeteoDay["tempMin"]);
            $noteDates->setMeteoBest($tabMeteoDay["meteoBest"]);
            $noteDates->setMeteoWorst($tabMeteoDay["meteoWorst"]);

            $em->persist($noteDates);
        }

        $em->flush();

        return $this->render('LaPoizWindBundle:BackOffice/Test:nbHoureNav.html.twig',
            array(
                'spot' => $spot,
                'tabNbHoure' => $tabNbHoureNav,
                'tabMeteo' => $tabMeteo,
                'message' => "",
                'saveSuccess' => true
            ));
    }

    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/dataHoureNav/1
     */
    public function tabDataHoureNavAction($id=null)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $spot = $em->find('LaPoizWindBundle:Spot', $id);

        if (!$spot) {
            return $this->render('LaPoizWindBundle:Default:errorBlock.html.twig',
                array('errMessage' => "Spot not find !"));
        }

        list($tabDataNbHoureNav,$tabDataMeteo)=NbHoureNav::createTabNbHoureNav($spot, $em);

        return $this->render('LaPoizWindBundle:BackOffice/Test:dataNbHoureNav.html.twig',
            array(
                'spot' => $spot,
                'tabNbHoure' => $tabDataNbHoureNav,
                'tabDataMeteo' => $tabDataMeteo,
                'message' => "",
                'saveSuccess' => true
            ));
    }


    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/1/add/SpotInfo
     */
    public function addSpotInfoAction($id=null, Request $request)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        if (isset($id) && $id!=-1)
        {
            $spot = $em->find('LaPoizWindBundle:Spot', $id);
            if (!$spot) {
                return $this->render('LaPoizWindBundle:BackOffice:errorPage.html.twig',
                    array('errMessage' => "No spot find !"));
            }
            $formNew=ManageInfoSpot::createNewForm($this, $spot);

            if ('POST' == $request->getMethod()) {
                $formNew->handleRequest($request);
                if ($formNew->isValid()) {
                    // form submit
                    $infoSpot = $formNew->getData();
                    ManageInfoSpot::saveNewInfoSpot($spot, $infoSpot, $em);
                    $formNew = ManageInfoSpot::createEditForm($this, $infoSpot);

                    return $this->render('LaPoizWindBundle:BackOffice/Spot/Ajax:editInfoSpot.html.twig', array(
                        'spot' => $spot,
                        'form' => $formNew->createView(),
                        'infoSpot' => $infoSpot
                    ));
                }
            }
            return $this->render('LaPoizWindBundle:BackOffice/Spot/Ajax:editInfoSpot.html.twig', array(
                'spot' => $spot,
                'form' => $formNew->createView()
            ));
        } else {
            return $this->render('LaPoizWindBundle:BackOffice:errorPage.html.twig',
                array('errMessage' => "Miss id of spot... !"));
        }
    }

    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/spotInfo/1
     */
    public function editSpotInfoAction($id=null, Request $request)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        if (isset($id) && $id!=-1)
        {
            $infoSpot = $em->find('LaPoizWindBundle:InfoSpot', $id);
            if (!$infoSpot)
            {
                return $this->render('LaPoizWindBundle:BackOffice:errorPage.html.twig',
                    array('errMessage' => "No infoSpot find !"));
            }

            $form = ManageInfoSpot::createEditForm($this, $infoSpot);
            $form->handleRequest($request);

            if ($form->isValid()) {
                        // Save
                        $infoSpot = $form->getData();
                        ManageInfoSpot::saveInfoSpot($infoSpot, $em);

                        return $this->render('LaPoizWindBundle:BackOffice/Spot/Ajax:editInfoSpot.html.twig', array(
                            'spot'  => $infoSpot->getSpot(),
                            'form' => $form->createView(),
                            'infoSpot' => $infoSpot
                        ));
            }
            // "GET"
            return $this->render('LaPoizWindBundle:BackOffice/Spot/Ajax:editInfoSpot.html.twig', array(
                    'spot' => $infoSpot->getSpot(),
                    'form' => $form->createView(),
                    'infoSpot' => $infoSpot
            ));
        } else {
            return $this->container->get('templating')->render(
                'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                array('errMessage' => "Miss id of spot... !"));
        }
    }

    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/remove/spotInfo/1
     */
    public function removeSpotInfoAction($id=null, Request $request)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        if (isset($id) && $id!=-1) {
            $infoSpot = $em->find('LaPoizWindBundle:InfoSpot', $id);
            if (!$infoSpot) {
                return $this->container->get('templating')->render(
                    'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                    array('errMessage' => "No infoSpot find !"));
            }


            // form submit
            $spot = $infoSpot->getSpot();
            try {
                ManageInfoSpot::deleteInfoSpot($infoSpot, $em);
                return $this->render('LaPoizWindBundle:BackOffice/Spot/Ajax:sucess.html.twig');
            } catch (\Exception $e) {
                return $this->render('LaPoizWindBundle:BackOffice:errorPage.html.twig',
                    array('errMessage' => "Problem lors de l'opération demandé:" . $e->getMessage()));
            }
        } else {
            return $this->render('LaPoizWindBundle:BackOffice:errorPage.html.twig',
                array('errMessage' => "Miss id of spot... !"));
        }
    }
}