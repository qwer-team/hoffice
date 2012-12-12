<?php

namespace HOffice\AdminBundle\Controller\House;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use HOffice\AdminBundle\Entity\House\HouseRepository;
use HOffice\AdminBundle\Entity\House\House;
use HOffice\AdminBundle\Form\House\HouseType;
/**
 * House\House controller.
 *
 * @Route("/itc/house")
 */
class HouseController extends Controller
{
    /**
     * Lists all House\House entities.
     *
     * @Route("/", name="house")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('HOfficeAdminBundle:House\House')->findAll();
        
        $deleteForm = array(); 
        foreach ($entities as $entity){
            $deleteForm[$entity->getId()] = $this->createDeleteForm($entity->getId())
                            ->createView();
        }
        
        return array(
            'entities' => $entities,
            'delete_form' => $deleteForm,
        );
    }

    /**
     * Finds and displays a House\House entity.
     *
     * @Route("/{id}/show", name="house_show")
     * @Template()
     */
    public function showAction($id)
    {
        
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HOfficeAdminBundle:House\House')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find House\House entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to create a new House\House entity.
     *
     * @Route("/new", name="house_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new House();
        $form   = $this->createForm(new HouseType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a new House\House entity.
     *
     * @Route("/create", name="house_create")
     * @Method("POST")
     * @Template("HOfficeAdminBundle:House\House:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new House();
        $form = $this->createForm(new HouseType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('house_edit', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing House\House entity.
     *
     * @Route("/{id}/edit", name="house_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HOfficeAdminBundle:House\House')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find House\House entity.');
        }

        $editForm = $this->createForm(new HouseType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing House\House entity.
     *
     * @Route("/{id}/update", name="house_update")
     * @Method("POST")
     * @Template("HOfficeAdminBundle:House\House:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HOfficeAdminBundle:House\House')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find House\House entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new HouseType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('house_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a House\House entity.
     *
     * @Route("/{id}/delete", name="house_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('HOfficeAdminBundle:House\House')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find House\House entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('house'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
