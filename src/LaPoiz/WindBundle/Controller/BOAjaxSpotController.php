<?php
namespace LaPoiz\WindBundle\Controller;

use LaPoiz\WindBundle\Command\CreateNbHoureCommand;
use LaPoiz\WindBundle\core\maree\MareeTools;
use LaPoiz\WindBundle\core\nbHoure\NbHoureMaree;
use LaPoiz\WindBundle\core\nbHoure\NbHoureMeteo;
use LaPoiz\WindBundle\core\nbHoure\NbHoureNav;
use LaPoiz\WindBundle\core\nbHoure\NbHoureWind;
use LaPoiz\WindBundle\core\note\ManageNote;
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
            $listSpotNotValid = $em->getRepository('LaPoizWindBundle:Spot')->findAllNotValid();

            return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice:index.html.twig',
                array(
                    'listSpot' => $listSpot,
                    'listSpotNotValid' => $listSpotNotValid,
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

            return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice:index.html.twig',
                array(
                    'listSpot' => $listSpot,
                    'listSpotNotValid' => $listSpotNotValid,
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
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/nbHoureNav/1
     */
    public function calculNbHoureNavAction($id=null)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $spot = $em->find('LaPoizWindBundle:Spot', $id);

        if (!$spot) {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:Default:errorBlock.html.twig',
                array('errMessage' => "Spot not find !"));
        }

        list($tabDataNbHoureNav,$tabDataMeteo)=NbHoureNav::createTabNbHoureNav($spot, $em);
        $tabNbHoureNav=NbHoureNav::calculateNbHourNav($tabDataNbHoureNav);
        $tabMeteo=NbHoureMeteo::calculateMeteoNav($tabDataMeteo);

        return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice/Test:nbHoureNav.html.twig',
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
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:Default:errorBlock.html.twig',
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

        return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice/Test:nbHoureNav.html.twig',
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
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:Default:errorBlock.html.twig',
                array('errMessage' => "Spot not find !"));
        }

        list($tabDataNbHoureNav,$tabDataMeteo)=NbHoureNav::createTabNbHoureNav($spot, $em);

        return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice/Test:dataNbHoureNav.html.twig',
            array(
                'spot' => $spot,
                'tabNbHoure' => $tabDataNbHoureNav,
                'tabDataMeteo' => $tabDataMeteo,
                'message' => "",
                'saveSuccess' => true
            ));
    }

}