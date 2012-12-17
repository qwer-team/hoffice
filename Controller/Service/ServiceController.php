<?php

namespace HOffice\AdminBundle\Controller\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use HOffice\AdminBundle\Entity\Service\Service;
use HOffice\AdminBundle\Form\Service\ServiceType;

/**
 * Service\Service controller.
 *
 * @Route("/service")
 */
class ServiceController extends Controller
{
    /**
     * Lists all Service\Service entities.
     *
     * @Route("/", name="service")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('HOfficeAdminBundle:Service\Service')
                       ->findAll();
        /*
        $repo = $em->getRepository('Gedmo\Loggable\Entity\LogEntry');
        $log = $repo->getLogEntries($entities[0]);
        
        
        echo "<pre>";
        foreach($log as $l)
        {
            print_r($l->getData());
        }
        echo "</pre>";
        */
        
        return array(
            'entities' => $entities,
        );
    }

    /**
     * Finds and displays a Service\Service entity.
     *
     * @Route("/{id}/show", name="service_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HOfficeAdminBundle:Service\Service')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Service\Service entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to create a new Service\Service entity.
     *
     * @Route("/new", name="service_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Service();
        $form   = $this->createForm(new ServiceType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a new Service\Service entity.
     *
     * @Route("/create", name="service_create")
     * @Method("POST")
     * @Template("HOfficeAdminBundle:Service\Service:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Service();
        $form = $this->createForm(new ServiceType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('service_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Service\Service entity.
     *
     * @Route("/{id}/edit", name="service_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HOfficeAdminBundle:Service\Service')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Service\Service entity.');
        }

        $editForm = $this->createForm(new ServiceType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Service\Service entity.
     *
     * @Route("/{id}/update", name="service_update")
     * @Method("POST")
     * @Template("HOfficeAdminBundle:Service\Service:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HOfficeAdminBundle:Service\Service')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Service\Service entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new ServiceType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('service_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Service\Service entity.
     *
     * @Route("/{id}/delete", name="service_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('HOfficeAdminBundle:Service\Service')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Service\Service entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('service'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
