<?php
namespace LaPoiz\WindBundle\Controller;

use LaPoiz\WindBundle\core\websiteDataManage\WebsiteGetData;
use LaPoiz\WindBundle\core\websiteDataManage\WebsiteManage;
use LaPoiz\WindBundle\Entity\Spot;
use LaPoiz\WindBundle\Entity\Contact;
use LaPoiz\WindBundle\Entity\DataWindPrev;
use LaPoiz\WindBundle\Form\MareeType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Validator\Constraints\Url;

class FOAjaxAskCreateSpotController extends Controller

{
    /**
     * FOAjaxAskCreateSpotController constructor.
     */
    public function __construct()
    {
    }


    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/ajax/spot/ask/create/structure
     *
     * Renvoie la structure HTMl de la page
     */
    public function structureAction()
    {
        return $this->render('LaPoizWindBundle:FrontOffice:AskNewSpot/structurePage.html.twig');

    }



    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/ajax/spot/ask/create/nav
     *
     * Renvoie la structure HTMl de la page
     */
    public function navAction()
    {
        return $this->render('LaPoizWindBundle:FrontOffice:AskNewSpot/blockNav.html.twig');

    }


    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/fo/ajax/spot/ask/create/step1
     *
     * Etape 1 : Vous
     */
    public function step1Action(Request $request)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $contact = new Contact();
        $form = $this->createForm('contactForm',$contact)
            ->add('captcha', 'genemu_captcha', array('mapped' => false))
            ->add("Let's go: étape 2",'submit');


        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $contact = $form->getData();
                try {
                    // enregistre le contact
                    $em->persist($contact);
                    $em->flush();
                } catch (\Exception $e) {
                    return $this->container->get('templating')->renderResponse(
                        'LaPoizWindBundle:FrontOffice:errorPage.html.twig',
                        array('errMessage' => "Exception : ".$e->getMessage()));
                }

                // -> step 2
                return $this->step2Action($contact->getId(), $request);
            }
        }
        return $this->render('LaPoizWindBundle:FrontOffice:AskNewSpot/step1.html.twig', array(
                'form' => $form->createView()
            ));
    }



    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/fo/ajax/spot/ask/create/step2/2
     *
     * Etape 2 : Creation du spot
     */
    public function step2Action($idContact = null, Request $request)
    {
        $spot = new Spot();
        $form = $this->createForm('spot',$spot);

        $form->add('actions', 'form_actions', [
                'buttons' => [
                    'save' => ['type' => 'submit', 'options' => ['label' => 'Etape 2: Les sites Internet de prévision de vent']],
                ]
            ]);


        if ($request->request->get("contactForm") == null) {
            if ('POST' == $request->getMethod()) {
                $form->handleRequest($request);

                if ($form->isValid()) {
                    $spot = $form->getData();
                    $em = $this->container->get('doctrine.orm.entity_manager');
                    $em->persist($spot);
                    $em->flush();

                    return $this->step3Action($spot->getId(), $idContact, $request);
                } else {
                    return $this->render('LaPoizWindBundle:FrontOffice:AskNewSpot/step2.html.twig', array(
                            'form' => $form->createView(),
                            'spot' => $spot,
                            'idContact' => $idContact
                        ));
                }
            }
        }
        return $this->render('LaPoizWindBundle:FrontOffice:AskNewSpot/step2.html.twig', array(
                'form' => $form->createView(),
                'spot' => $spot,
                'idContact' => $idContact
            ));

    }


    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/fo/ajax/spot/ask/create/step3/2/1
     *
     * Etape 3: ajout des website
     */
    public function step3Action($id=null, $idContact=null, Request $request)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        if (isset($id) && $id!=-1)
        {
            $spot = $em->find('LaPoizWindBundle:Spot', $id);
            if (!$spot)
            {
                return $this->container->get('templating')->renderResponse(
                    'LaPoizWindBundle:FrontOffice:errorPage.html.twig',
                    array('errMessage' => "No spot find !"));
            }
            $dataWindPrev = new DataWindPrev();
            $dataWindPrev->setSpot($spot);
            $form = $this->createForm('dataWindPrevForm',$dataWindPrev)
                ->add('Ajoute et test le site','submit');


            if ($request->request->get("spot") == null) {
                if ('POST' == $request->getMethod()) {
                    $form->handleRequest($request);

                    if ($form->isValid()) {
                        // form submit
                        $dataWindPrev = $form->getData();

                        // Récupération du webSite grace à l'url
                        $website=WebsiteManage::getWebSiteEntityFromURL($dataWindPrev->getUrl(),$em);
                        if ($website!=null) {
                            $dataWindPrevInDB=$em->getRepository('LaPoizWindBundle:DataWindPrev')->getWithWebsiteAndSpot($website,$spot);
                            if ($dataWindPrevInDB === null) {
                                $dataWindPrev->setWebsite($website);
                                // test si l'url est OK
                                $webSite = WebsiteGetData::getWebSiteObject($dataWindPrev);
                                if ($webSite->isDataWindPrevOK($dataWindPrev)) {
                                    // Il est possible de parser le site de cet URL

                                    if ($dataWindPrev->getWebsite()->getNom() == WebsiteGetData::windguruName) {
                                        $dataWindPrevWindGuruPro = clone $dataWindPrev;
                                        $dataWindPrevWindGuruPro->getWebsite()->removeDataWindPrev($dataWindPrevWindGuruPro);
                                        $windGuruProWebsite = $em->getRepository('LaPoizWindBundle:WebSite')->findByNom(WebsiteGetData::windguruProName)[0];
                                        $dataWindPrevWindGuruPro->setWebsite($windGuruProWebsite);
                                        $this->saveDataWindPrev($spot, $dataWindPrevWindGuruPro, $em);
                                    }

                                    $this->saveDataWindPrev($spot, $dataWindPrev, $em);

                                    // clean the form and display it
                                    $dataWindPrev = new DataWindPrev();
                                    $dataWindPrev->setSpot($spot);
                                    $form = $this->createForm('dataWindPrevForm', $dataWindPrev)
                                        ->add('Ajoute et test le site', 'submit');

                                    return $this->render('LaPoizWindBundle:FrontOffice:AskNewSpot/blockAddSite.html.twig', array(
                                        'form' => $form->createView(),
                                        'spot' => $spot,
                                        'idContact' => $idContact
                                    ));
                                } else {
                                    // URL n'est pas bonne
                                    return new JsonResponse(array('message' => $website->getNom().' est déjà spécifié pour ce spot'), 419);
                                }
                            } else {
                                // URL n'est pas bonne
                                return new JsonResponse(array('message' => 'Impossible de parser l URL'), 419);
                            }
                        } else {
                            // URL n'est pas bonne
                            return new JsonResponse(array('message' => 'Cette URL ne correspond à aucun site parser par LaPoizWind.'), 419);
                        }
                    }
                    /*else {
                        return new Response($request);
                    }*/
                }
            }

            return $this->render('LaPoizWindBundle:FrontOffice:AskNewSpot/step3.html.twig', array(
                    'spot' => $spot,
                    'form' => $form->createView(),
                    'idContact' => $idContact
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
     * http://localhost/Wind/web/app_dev.php/fo/ajax/spot/ask/create/step4/1
     *
     * Etape 4: La marée
     */
    public function step4Action($id=null, $idContact=null, Request $request)
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

            return $this->render('LaPoizWindBundle:FrontOffice:AskNewSpot/step4.html.twig', array(
                    'form' => $form->createView(),
                    'spot' => $spot,
                    'idContact' => $idContact
                ));

        } else {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                array('errMessage' => "Miss id of spot... !"));
        }

    }


    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/fo/ajax/spot/ask/create/step5/1
     *
     * Etape 5: Résumé
     */
    public function step5Action($id=null, $idContact=null, Request $request)
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
            if (isset($idContact) && $idContact!=-1)
            {
                $contact = $em->find('LaPoizWindBundle:Contact', $idContact);
                if (!$contact)
                {
                    return $this->container->get('templating')->renderResponse(
                        'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                        array('errMessage' => "No contact find !"));
                }

                $comment = new Contact();
                $form = $this->createForm('commentForm',$comment)
                    ->add('Envoie de la demande','submit');

                return $this->render('LaPoizWindBundle:FrontOffice:AskNewSpot/step5.html.twig', array(
                        'spot' => $spot,
                        'contact' => $contact,
                        'form' => $form->createView()
                    ));
            }
        } else {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                array('errMessage' => "Miss id of spot... !"));
        }

    }


    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/fo/ajax/spot/ask/create/send
     *
     * Etape 6 : Send
     */
    public function sendAction($id=null, $idContact=null, Request $request)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        $spot = $em->find('LaPoizWindBundle:Spot', $id);
        $contact = $em->find('LaPoizWindBundle:Contact', $idContact);

        $comment = new Contact();
        $form = $this->createForm('comentForm',$comment);

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $comment = $form->getData();

                try {
                    // enregistre le comment
                    if ($comment->getComment()===null) {
                        $comment->setComment("");
                    }
                    $contact->setComment($comment->getComment()."  ***** spot add:".$spot->getNom());
                    $em->persist($contact);
                    $em->flush();
                } catch (\Exception $e) {
                    return $this->container->get('templating')->renderResponse(
                        'LaPoizWindBundle:FrontOffice:errorPage.html.twig',
                        array('errMessage' => "Exception : ".$e->getMessage()));
                }
            }
        }
        return $this->render('LaPoizWindBundle:FrontOffice:AskNewSpot/step1.html.twig', array(
                'form' => $form->createView()
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