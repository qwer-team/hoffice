<?php

namespace HOffice\AdminBundle\Controller\House;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use HOffice\AdminBundle\Entity\House\Apartment;
use HOffice\AdminBundle\Form\House\ApartmentType;

/**
 * House\Apartment controller.
 *
 * @Route("/itc/apartment")
 */
class ApartmentController extends Controller
{
    /**
     * Lists all House\Apartment entities.
     *
      @Route("/{coulonpage}/{houseId}", name="apartment",
     * requirements={"houseId" = "\d+", "coulonpage" = "\d+"}, 
     * defaults={ "houseId"=null,"coulonpage"="20"})
     * 
     * @Template()
     */
    public function indexAction($houseId=null, $coulonpage = 20)
    {
        $em = $this->getDoctrine()->getManager();

        
        $repo = $em->getRepository('HOfficeAdminBundle:House\Apartment');
        if(null === $houseId)
        {
            $qb = $repo->createQueryBuilder('M')
                        ->orderBy('M.kod');
        }
        else
        {
            $qb = $repo->createQueryBuilder('M')
                        ->where('M.houseId = :houseId')
                        ->setParameter('houseId', $houseId)
                        ->orderBy('M.kod');
            
        }
         
        $paginator = $this->get('knp_paginator');
        $entities = $paginator->paginate(
            $qb,
            $this->get('request')->query->get( 'page', 1 )/*page number*/,
            $coulonpage/*limit per page*/
        );
        
        $deleteForm = array(); 
        foreach ($entities as $entity){
            $deleteForm[$entity->getId()] = $this->createDeleteForm($entity->getId())
                            ->createView();
        }
        
        return array(
            'entities' => $entities,
            'delete_form' => $deleteForm,
            'houseId' => $houseId,
            'coulonpage'   => $coulonpage,
        );
    }
    /**
     * Finds and displays a House\Apartment entity.
     *
     * @Route("/{id}/show", name="apartment_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HOfficeAdminBundle:House\Apartment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find House\Apartment entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to create a new House\Apartment entity.
     *
     * @Route("/new", name="apartment_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Apartment();
        $form   = $this->createForm(new ApartmentType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a new House\Apartment entity.
     *
     * @Route("/create", name="apartment_create")
     * @Method("POST")
     * @Template("HOfficeAdminBundle:House\Apartment:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Apartment();
        $form = $this->createForm(new ApartmentType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('apartment_edit', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing House\Apartment entity.
     *
     * @Route("/{id}/edit", name="apartment_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HOfficeAdminBundle:House\Apartment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find House\Apartment entity.');
        }

        $editForm = $this->createForm(new ApartmentType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing House\Apartment entity.
     *
     * @Route("/{id}/update", name="apartment_update")
     * @Method("POST")
     * @Template("HOfficeAdminBundle:House\Apartment:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HOfficeAdminBundle:House\Apartment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find House\Apartment entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new ApartmentType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('apartment_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a House\Apartment entity.
     *
     * @Route("/{id}/delete", name="apartment_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('HOfficeAdminBundle:House\Apartment')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find House\Apartment entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('apartment'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
