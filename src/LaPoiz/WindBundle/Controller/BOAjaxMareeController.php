<?php
namespace LaPoiz\WindBundle\Controller;

use LaPoiz\WindBundle\Entity\DataWindPrev;
use LaPoiz\WindBundle\Entity\WebSite;
use LaPoiz\WindBundle\Form\MareeType;
use LaPoiz\WindBundle\Form\SpotType;
use LaPoiz\WindBundle\Form\DataWindPrevType;
use LaPoiz\WindBundle\core\maree\MareeGetData;
use LaPoiz\WindBundle\core\note\NoteMaree;
use LaPoiz\WindBundle\core\note\ManageNote;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
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
            //$prevMaree=MareeGetData::getMaree($spot->getMareeURL());
            $prevMaree=MareeGetData::getMareeForXDays($spot->getMareeURL(),10);

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
        return $this->spotMareeEditAction($id,$request);
/*        $em = $this->container->get('doctrine.orm.entity_manager');

        if (isset($id) && $id!=-1)
        {
            $spot = $em->find('LaPoizWindBundle:Spot', $id);
            if (!$spot)
            {
                return $this->container->get('templating')->renderResponse(
                    'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                    array('errMessage' => "No spot find !"));
            }

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
 */ }

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

            return $this->render('LaPoizWindBundle:BothOffice/Spot/Ajax/Maree:mareeEdit.html.twig', array(
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
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/maree/save/1
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
                'spot' => $spot,
                'message' => "",
                'saveSuccess' => true
            ));
    }


    /**
     * @Template()
     * Note les marées
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/maree/note/1
     */
    public function mareeLaunchNoteAction($id)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $spot = $em->find('LaPoizWindBundle:Spot', $id);

        if (!$spot)
        {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:Default:errorBlock.html.twig',
                array('errMessage' => "Spot not find !"));
        }


        $tabNotes = array();
        // Pour les 7 prochains jours
        $day= new \DateTime("now");
        for ($nbPrevision=0; $nbPrevision<7; $nbPrevision++) {
            $tabNotes[$day->format('Y-m-d')]=array();
            $day->modify('+1 day');
        }

        //********** Marée **********
        // récupére la marée du jour
        // Note la marée en fonction des restrictions
        $listeMareeFuture = $em->getRepository('LaPoizWindBundle:MareeDate')->getFuturMaree($spot);
        foreach ($listeMareeFuture as $mareeDate) {
            $tabNotes = NoteMaree::calculNoteMaree($spot, $tabNotes, $mareeDate);
        }

        return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice/Spot/Ajax/Maree:mareeDisplayNote.html.twig',
            array(
                'tabNotes' => $tabNotes,
                'spot' => $spot,
                'message' => "",
                'saveSuccess' => true
            ));
    }


    /**
     * @Template()
     * Note les marées
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/maree/note/save/1
     */
    public function mareeSaveNoteAction($id)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $spot = $em->find('LaPoizWindBundle:Spot', $id);

        if (!$spot)
        {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:Default:errorBlock.html.twig',
                array('errMessage' => "Spot not find !"));
        }

        // On efface les vielles notes (avant aujourd'hui)
        ManageNote::deleteOldData($spot, $em);

        $tabNotes = array();
        // Pour les 7 prochains jours
        $day= new \DateTime("now");
        for ($nbPrevision=0; $nbPrevision<7; $nbPrevision++) {
            $tabNotes[$day->format('Y-m-d')]=array();
            $day->modify('+1 day');
        }


        //********** Marée **********
        // récupére la marée du jour
        // Note la marée en fonction des restrictions
        $listeMareeFuture = $em->getRepository('LaPoizWindBundle:MareeDate')->getFuturMaree($spot);
        foreach ($listeMareeFuture as $mareeDate) {
            $noteDate = ManageNote::getNotesDate($spot,$mareeDate->getDatePrev(), $em);
            $tabNotes = NoteMaree::calculNoteMaree($spot, $tabNotes, $mareeDate);
            $noteDate->setNoteMaree($tabNotes[$mareeDate->getDatePrev()->format('Y-m-d')]["marée"]);
            $em->persist($noteDate);
        }
        $em->flush();

        return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice/Spot/Ajax:displayNote.html.twig',
            array(
                'spot' => $spot,
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

    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/maree/dateCoef/idURLInfoMaree
     */
    public function getDateCoefAction($idURLInfoMaree=null, Request $request)
    {
        // Récupere la liste des amplitudes de marée: http://maree.info/$idURLInfoMaree/calendrier
        // Parse le tableau et récupére URL de la marée à coef le plus haut, de la marée le coef le plus bas, et de la marée à coef 80
        // sur la base de :
        // Tous les Table classe="CalendrierMois"
        //  Pour chaque TR
        //      récupére les TD class="coef"
        //          compare avec max, min et 80
        //          si OK
        //              get id de TD class="event" (en enlevant le D du début)
        //              get title de TD class="DW"

        // envoie les jours en JSON

        return new JsonResponse(MareeGetData::getExtremMaree($idURLInfoMaree));

    }


    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/maree/{idURLInfoMaree}/forDay/{idDateURLInfoMaree}
     * dateMaree = 20150106
     */
    public function getMareeForDayAction($idURLInfoMaree=null,$idDateURLInfoMaree=null, Request $request)
    {
        // Récupere la page: http://maree.info/$idURLInfoMaree?d=$idDateURLInfoMaree
        // Parse avec ce qui est déjà fait dans core -> MareeGetData
        // envoie la hauteur marée haute et marée basse en JSON
        return new JsonResponse(MareeGetData::getHauteurMaree($idURLInfoMaree, $idDateURLInfoMaree));
    }

}