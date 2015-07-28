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
}