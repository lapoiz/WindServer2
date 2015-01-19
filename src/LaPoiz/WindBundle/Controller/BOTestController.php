<?php
namespace LaPoiz\WindBundle\Controller;

use LaPoiz\WindBundle\Form\TestGetMareeType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use LaPoiz\WindBundle\Entity\Spot;
use LaPoiz\WindBundle\core\maree\MareeGetData;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Output\NullOutput;

class BOTestController extends Controller

{
  /**
     * @Template()
     */
  public function indexAction()
  {
    $message = '';
    return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice/Test:index.html.twig',
      array('message' => $message));
  }

    /**
     * @Template()
     */
    public function mareeAction(Request $request)
    {
        $message = '';
        $mareeURL = '';
        $prevMaree = null;
        $spot = null;

        $em = $this->container->get('doctrine.orm.entity_manager');
        // récupere tous les spots
        $spots = $em->getRepository('LaPoizWindBundle:Spot')->findAll();
        $form = $this->createForm('testGetMaree',$spots);

        if ('POST' == $request->getMethod()) {
            $result = 'Elem post';
            //$form->bindRequest($request);

            $form->handleRequest($request);
            if ($form->isValid()) {
                // form submit
                $spot = $form->get('spot')->getData();
                //$spot = $em->getRepository('LaPoizWindBundle:Spot')->find();

                $mareeURL = $spot->getParameter()->getMareeURL();
                if (!empty($mareeURL)) {
                    $prevMaree = MareeGetData::getMaree($mareeURL);
                }
            }
        }


        return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice/Test:maree.html.twig',
            array(
                'listSpot' => $spots,
                'spot' => $spot,
                'form' => $form->createView(),
                'mareeURL' => $mareeURL,
                'prevMaree' => $prevMaree
            ));


    }

     /**
     * @Template()
     * Sauvegarde les prévisions de marée en prenant en compte ce qui existe déjà dans la BD
     */
    public function mareeSaveAction($id)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $spots = $em->getRepository('LaPoizWindBundle:Spot')->findAll();
        $spot = $em->find('LaPoizWindBundle:Spot', $id);
        $form = $this->createForm('testGetMaree',$spots);

        if (!$spot)
        {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:Default:errorBlock.html.twig',
                array('errMessage' => "No spot find !"));
        }

        $mareeURL = $spot->getParameter()->getMareeURL();
        if (!empty($mareeURL)) {
            $prevMaree = MareeGetData::getMaree($mareeURL);
            MareeGetData::saveMaree($spot,$prevMaree,$em,new NullOutput());
        }

        $mareeDateDB = $em->getRepository('LaPoizWindBundle:MareeDate')->findLastPrev(10, $spot);

        return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice/Test:maree.html.twig',
            array(
                'listSpot' => $spots,
                'spot' => $spot,
                'form' => $form->createView(),
                'mareeURL' => $mareeURL,
                'prevMaree' => $prevMaree,
                'mareeDateDB' => $mareeDateDB
            ));
    }
}