<?php

namespace HOffice\AdminBundle\Controller\Reversal;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use HOffice\AdminBundle\Entity\Reversal\Reversal;
use HOffice\AdminBundle\Entity\Reversal\tag;
use HOffice\AdminBundle\Form\Reversal\ReversalType;
use \Itc\DocumentsBundle\Entity\Pd\Pdl;

/**
 * Reversal\Reversal controller.
 *
 * @Route("/reversal")
 */
class ReversalController extends Controller
{
    /**
     * Lists all Reversal\Reversal entities.
     *
     * @Route("/", name="reversal")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('HOfficeAdminBundle:Reversal\Reversal')
                       ->leftPdlines();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Finds and displays a Reversal\Reversal entity.
     *
     * @Route("/{id}/show", name="reversal_show")
     * @Template()
     */
    public function showAction($id)
    {
        return $this->redirect($this->generateUrl('reversal_edit', array('id' => $id)));
    }

    /**
     * Displays a form to create a new Reversal\Reversal entity.
     *
     * @Route("/new", name="reversal_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Reversal();

        $oa1array = array( 1, 2, 3, 4, 5, 6 ); //services

        foreach( $oa1array as $oa1 ){

            $pdline1 = new Pdl; 
            $pdline1->setOa1( $oa1 ) ;
            $pdline1->setN( $oa1 ) ;
            $entity->getPdlines()->add( $pdline1 );

        }
        
        $form   = $this->createForm( new ReversalType(), $entity );

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    
    function createPdl( $entity = NULL ){

        if( $entity === NULL )
            $entity = new Reversal();

        $oa1array = array( 1, 2, 3, 4, 5, 6 ); //services

        foreach( $oa1array as $oa1 ){

            $pdline1 = new Pdl; 
            $pdline1->setOa1( $oa1 ) ;
            $pdline1->setN( $oa1 ) ;
            $entity->getPdlines()->add( $pdline1 );

        }

        return $entity;
    }

    /**
     * Creates a new Reversal\Reversal entity.
     *
     * @Route("/create", name="reversal_create")
     * @Method("POST")
     * @Template("HOfficeAdminBundle:Reversal\Reversal:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Reversal();

        $form = $this->createForm(new ReversalType(), $entity);
        $form->bind( $request );

        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();

            $em->persist($entity);

            foreach( $entity->getPdlines() as $f => $v ) {

                $v->setPdid( $entity );
                $em->persist( $v );

            }
            
            $em->flush();

            return $this->redirect($this->generateUrl('reversal_edit', array('id' => $entity->getId())));

        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Reversal\Reversal entity.
     *
     * @Route("/{id}/edit", name="reversal_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HOfficeAdminBundle:Reversal\Reversal')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Reversal\Reversal entity.');
        }

        $editForm = $this->createForm(new ReversalType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Reversal\Reversal entity.
     *
     * @Route("/{id}/update", name="reversal_update")
     * @Method("POST")
     * @Template("HOfficeAdminBundle:Reversal\Reversal:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HOfficeAdminBundle:Reversal\Reversal')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Reversal\Reversal entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new ReversalType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('reversal_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Reversal\Reversal entity.
     *
     * @Route("/{id}/delete", name="reversal_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('HOfficeAdminBundle:Reversal\Reversal')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Reversal\Reversal entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('reversal'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
