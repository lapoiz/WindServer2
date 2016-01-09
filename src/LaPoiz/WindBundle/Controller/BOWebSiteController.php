<?php

namespace LaPoiz\WindBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use LaPoiz\WindBundle\Entity\WebSite;
use LaPoiz\WindBundle\Form\WebSiteType;

/**
 * WebSite controller.
 */
class BOWebSiteController extends Controller
{


     /**
     * Finds and displays a edit WebSite entity.
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAllValid();
        $listRegion = $em->getRepository('LaPoizWindBundle:Region')->findAllOrderByNumDisplay();
        $listSpotsWithoutRegion = $em->getRepository('LaPoizWindBundle:Spot')->findAllWithoutRegion();
        $listWebsites = $em->getRepository('LaPoizWindBundle:WebSite')->findAll();

        $website = $em->getRepository('LaPoizWindBundle:WebSite')->find($id);

        if (!$website) {
            throw $this->createNotFoundException('Unable to find WebSite entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($website);


        return $this->render('LaPoizWindBundle:BackOffice/Website:edit.html.twig', array(
            'website'  => $website,
            'delete_form' => $deleteForm->createView(),
            'edit_form' => $editForm->createView(),

            'listSpot' => $listSpot,
            'listSpotsWithoutRegion' => $listSpotsWithoutRegion,
            'listRegion' => $listRegion,
            'listWebsites' => $listWebsites
        ));
    }

    /**
     * Creates a new WebSite entity.
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $listSpot = $em->getRepository('LaPoizWindBundle:Spot')->findAllValid();
        $listRegion = $em->getRepository('LaPoizWindBundle:Region')->findAllOrderByNumDisplay();
        $listSpotsWithoutRegion = $em->getRepository('LaPoizWindBundle:Spot')->findAllWithoutRegion();
        $listWebsites = $em->getRepository('LaPoizWindBundle:WebSite')->findAll();

        $website = new WebSite();
        $form = $this->createCreateForm($website);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->persist($website);
            $em->flush();

            $editForm = $this->createEditForm($website);
            $deleteForm = $this->createDeleteForm($website->getId());

            return $this->render('LaPoizWindBundle:BackOffice/Website:edit.html.twig', array(
                'website'  => $website,
                'delete_form' => $deleteForm->createView(),
                'edit_form' => $editForm->createView(),

                'listSpot' => $listSpot,
                'listSpotsWithoutRegion' => $listSpotsWithoutRegion,
                'listRegion' => $listRegion,
                'listWebsites' => $listWebsites
            ));
        }
        return $this->render('LaPoizWindBundle:BackOffice/WebSite:new.html.twig', array(
            'website'  => $website,
            'form' => $form->createView(),

            'listSpot' => $listSpot,
            'listSpotsWithoutRegion' => $listSpotsWithoutRegion,
            'listRegion' => $listRegion,
            'listWebsites' => $listWebsites
        ));
    }

    /**
     * Deletes a WebSite entity.
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('LaPoizWindBundle:WebSite')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find WebSite entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('_bo_index'));
    }



    /**
     * Creates a form to delete a WebSite entity by id.
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('_bo_website_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    /**
     * Creates a form to edit a WebSite entity.
     * @param WebSite $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(WebSite $entity)
    {
        $form = $this->createForm(new WebSiteType(), $entity, array(
            'action' => $this->generateUrl('_bo_website_edit', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Creates a form to create a WebSite entity.
     * @param WebSite $entity The entity
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(WebSite $entity)
    {
        $form = $this->createForm(new WebSiteType(), $entity, array(
            'action' => $this->generateUrl('_bo_website_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }
}
