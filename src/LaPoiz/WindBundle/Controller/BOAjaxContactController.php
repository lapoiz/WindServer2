<?php
namespace LaPoiz\WindBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Validator\Constraints\Url;

class BOAjaxContactController extends Controller

{

    /**
     * @Template()
     *
     * http://localhost/Wind/web/app_dev.php/admin/BO/ajax/spot/delete/1
     *
     * Used for delete spot not valide
     */
    public function deleteAction($id=null)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');

        if (isset($id) && $id!=-1)
        {
            $contact = $em->find('LaPoizWindBundle:Contact', $id);
            if (!$contact)
            {
                return $this->container->get('templating')->renderResponse(
                    'LaPoizWindBundle:BackOffice:errorPage.html.twig',
                    array('errMessage' => "No contact find !"));
            }
            $em->remove($contact);
            $em->flush();

            return $this->render('LaPoizWindBundle:BothOffice:Ajax/ok.html.twig');
        } else {
            return $this->render('LaPoizWindBundle:BackOffice:errorPage.html.twig',
                array('errMessage' => "Miss id of contact... !"));
        }
    }


}