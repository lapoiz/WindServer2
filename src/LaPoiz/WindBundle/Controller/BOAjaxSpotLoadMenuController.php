<?php
namespace LaPoiz\WindBundle\Controller;

use LaPoiz\WindBundle\Command\CreateNbHoureCommand;
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

class BOAjaxSpotLoadMenuController extends Controller

{

    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/1/load/infoSite
     */
    public function loadInfoSiteAction($id=null)
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

            return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice/Spot/Ajax/Menu:infoSpot.html.twig',
                array(
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
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/1/load/webSIte
     */
    public function loadWebSiteAction($id=null)
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

            return $this->container->get('templating')->renderResponse('LaPoizWindBundle:BackOffice/Spot/Ajax/Menu:webSite.html.twig',
                array(
                    'spot' => $spot
                )
            );
        } else {
            return $this->container->get('templating')->renderResponse(
                'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                array('errMessage' => "Miss id of spot... !"));
        }
    }

}