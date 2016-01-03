<?php
namespace LaPoiz\WindBundle\Controller;


use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;
use LaPoiz\WindBundle\core\note\ManageNote;
use LaPoiz\WindBundle\core\websiteDataManage\WebsiteManage;
use LaPoiz\WindBundle\Entity\DataWindPrev;
use LaPoiz\WindBundle\core\note\NoteWind;
use LaPoiz\WindBundle\core\note\NoteMeteo;
use LaPoiz\WindBundle\core\note\NoteTemp;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Validator\Constraints\DateTime;
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
                    // Uniquement url et spot.

                    // Récupération du webSite grace à l'url
                    $website=WebsiteManage::getWebSiteEntityFromURL($dataWindPrev->getUrl(),$em);
                    if ($website!=null) {
                        // Check si le couple spot+website n'existe pas déjà ...
                        $dataWindPrevInDB=$em->getRepository('LaPoizWindBundle:DataWindPrev')->getWithWebsiteAndSpot($website,$spot);

                        if ($dataWindPrevInDB === null) {
                            // OK il n'existe pas dans la BD -> on peut l'ajouter
                            $dataWindPrev->setWebsite($website);

                            if ($dataWindPrev->getWebsite()->getNom() == WebsiteGetData::windguruName) {
                                $dataWindPrevWindGuruPro = clone $dataWindPrev;
                                $dataWindPrevWindGuruPro->getWebsite()->removeDataWindPrev($dataWindPrevWindGuruPro);
                                $windGuruProWebsite = $em->getRepository('LaPoizWindBundle:WebSite')->findByNom(WebsiteGetData::windguruProName)[0];
                                $dataWindPrevWindGuruPro->setWebsite($windGuruProWebsite);
                                $this->saveDataWindPrev($spot, $dataWindPrevWindGuruPro, $em);
                            }

                            $this->saveDataWindPrev($spot, $dataWindPrev, $em);


                            return $this->render('LaPoizWindBundle:BackOffice/Spot/Ajax:addSite.html.twig', array(
                                    'spot' => $spot,
                                    'form' => $form->createView(),
                                    'create' => false
                                )
                            );
                        } else {
                            // Le couple spot et website existe dans la BD
                            $form->get('url')->addError(new FormError('Ce site de prévision est déjà existant pour ce spot.'));
                        }
                    } else {
                        // pas trouvé dfe website correspondant
                        $form->get('url')->addError(new FormError('Site internet non géré par laPoiz.com'));
                    }
                }
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

            $dataWindPrev->getWebsite()->removeDataWindPrev($dataWindPrev);
            $dataWindPrev->getSpot()->removeDataWindPrev($dataWindPrev);

            $em->remove($dataWindPrev);
            $em->flush();

            return $this->forward('LaPoizWindBundle:BO:editSpot', array(
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

    /**
     * @Template()
     * Note la meteo: vent +  pluie + soleil + Temp
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/meteo/note/1
     */
    public function spotLaunchMeteoNoteAction($id)
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
        $tabListePrevisionDate = array(); // tableau des liste des PrevisionDate, cahque cellule correspondant à une dateprev

        // Pour les 7 prochains jours
        $day= new \DateTime("now");
        for ($nbPrevision=0; $nbPrevision<7; $nbPrevision++) {
            $tabNotes[$day->format('Y-m-d')]=array();
            $tabListePrevisionDate[$day->format('Y-m-d')]=array();
            $day->modify('+1 day');
        }

        //********** Meteo **********

        //list des PrevisionDate pour les prochain jour, pour le spot pour tous les websites
        $listALlPrevisionDate = $this->getDoctrine()->getRepository('LaPoizWindBundle:PrevisionDate')->getPrevDateAllWebSiteNextDays($spot);

        foreach ($listALlPrevisionDate as $previsionDate) {
            // ajouter au tableau de la cellule du jour de $tabListePrevisionDate
            $tabListePrevisionDate[$previsionDate->getDatePrev()->format('Y-m-d')][]=$previsionDate;
        }

        foreach ($tabNotes as $keyDate=>$note) {
            if ($tabListePrevisionDate[$keyDate] != null && count($tabListePrevisionDate[$keyDate])>0) {
                $tabNotes[$keyDate]["wind"] = NoteWind::calculNoteWind($tabListePrevisionDate[$keyDate]);
                $tabNotes[$keyDate]["meteo"] = NoteMeteo::calculNoteMeteo($tabListePrevisionDate[$keyDate]);
                $tabNotes[$keyDate]["temp"] = NoteTemp::calculNoteTemp($tabListePrevisionDate[$keyDate]);
            }
        }


        return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice/Spot/Ajax:meteoDisplayNote.html.twig',
            array(
                'tabNotes' => $tabNotes,
                'spot' => $spot,
                'message' => "",
                'saveSuccess' => true
            ));
    }

    /**
     * @Template()
     * Note la meteo: pluie + soleil
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/meteo/note/save/1
     */
    public function spotSaveMeteoNoteAction($id)
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
        $tabListePrevisionDate = array(); // tableau des liste des PrevisionDate, cahque cellule correspondant à une dateprev

        // Pour les 7 prochains jours
        $day= new \DateTime("now");
        for ($nbPrevision=0; $nbPrevision<7; $nbPrevision++) {
            $tabNotes[$day->format('Y-m-d')]=-1;
            $tabListePrevisionDate[$day->format('Y-m-d')]=array();
            $day->modify('+1 day');
        }

        //********** Meteo **********


        // On efface les vielle données
        ManageNote::deleteOldData($spot, $em);

        //list des PrevisionDate pour les prochain jour, pour le spot pour tous les websites
        $listALlPrevisionDate = $this->getDoctrine()->getRepository('LaPoizWindBundle:PrevisionDate')->getPrevDateAllWebSiteNextDays($spot);

        foreach ($listALlPrevisionDate as $previsionDate) {
            // ajouter au tableau de la cellule du jour de $tabListePrevisionDate
            $tabListePrevisionDate[$previsionDate->getDatePrev()->format('Y-m-d')][]=$previsionDate;
        }

        foreach ($tabNotes as $keyDate=>$note) {
            if ($tabListePrevisionDate[$keyDate] != null && count($tabListePrevisionDate[$keyDate])>0) {
                $noteDate = ManageNote::getNotesDate($spot,\DateTime::createFromFormat('Y-m-d',$keyDate), $em);
                $noteDate->setNoteMeteo(NoteMeteo::calculNoteMeteo($tabListePrevisionDate[$keyDate]));
                $noteDate->setNoteWind(NoteWind::calculNoteWind($tabListePrevisionDate[$keyDate]));
                $noteDate->setNoteTemp(NoteTemp::calculNoteTemp($tabListePrevisionDate[$keyDate]));
                $em->persist($noteDate);
            }
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
     * @param $spot
     * @param $dataWindPrev
     * @param $em
     */
    private function saveDataWindPrev($spot, $dataWindPrev, $em)
    {
        $spot->addDataWindPrev($dataWindPrev);
        $site = $dataWindPrev->getWebsite();
        $site->addDataWindPrev($dataWindPrev);

        $em->persist($dataWindPrev);
        $em->persist($site);
        $em->persist($spot);
        $em->flush();
    }
}