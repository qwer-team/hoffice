<?php

namespace HOffice\AdminBundle\Controller\Invoice;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use HOffice\AdminBundle\Entity\Invoice\Invoice;
use HOffice\AdminBundle\Form\Invoice\InvoiceType;
use Symfony\Component\Locale\Locale;
use Itc\AdminBundle\Tools\LanguageHelper;
use Itc\AdminBundle\ItcAdminBundle;

/**
 * Invoice\Invoice controller.
 *
 * @Route("/invoice")
 */
class InvoiceController extends Controller
{
    /**
     * Lists all Invoice\Invoice entities.
     *
     * @Route("/", name="invoice")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('HOfficeAdminBundle:Invoice\Invoice')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Finds and displays a Invoice\Invoice entity.
     *
     * @Route("/{id}/show", name="invoice_show")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HOfficeAdminBundle:Invoice\Invoice')->find( $id );

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Invoice\Invoice entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to create a new Invoice\Invoice entity.
     *
     * @Route("/new", name="invoice_new")
     * @Template()
     */
    public function newAction()
    {
        $languages  = LanguageHelper::getLanguages();
        $locale =  LanguageHelper::getLocale();
        $context = ItcAdminBundle::getContainer();
        $entity = new Invoice();
        $form   = $this->createForm(new InvoiceType(), $entity);
        $usr = $context->get('security.context')->getToken()->getUser()->getUserName();
        $date = date("d/m/Y");
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'locale' => $locale,
            'languages' => $languages,
            'date' => $date,
            'user' => $usr,
        );
    }

    /**
     * Creates a new Invoice\Invoice entity.
     *
     * @Route("/create", name="invoice_create")
     * @Method("POST")
     * @Template("HOfficeAdminBundle:Invoice\Invoice:edit.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Invoice();
        $form = $this->createForm(new InvoiceType(), $entity);
        $form->bind($request);

        if ($form->isValid()) 
        {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $entity->setStatus(1);
            $entity->setPdtypeId(1);
            $entity->setOa1(0);
            $entity->setOa2(0);
            $entity->setSumma1(0);
            $entity->setSumma2(0);
            $entity->setSumma3(0);
            $em->flush();
            return $this->redirect($this->generateUrl('invoice_edit', array('id' => $entity->getId())));
        }else{
            echo $form->getErrorsAsString();
        }
        $languages  = LanguageHelper::getLanguages();
        $locale =  LanguageHelper::getLocale();
        $context = ItcAdminBundle::getContainer();
        $usr = $context->get('security.context')->getToken()->getUser()->getUserName();
        $date = date("d/m/Y");
        
        
        
        
        
        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'locale' => $locale,
            'languages' => $languages,
            'date' => $date,
            'user' => $usr,
        );
    }

    /**
     * Displays a form to edit an existing Invoice\Invoice entity.
     *
     * @Route("/{id}/edit", name="invoice_edit")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HOfficeAdminBundle:Invoice\Invoice')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Invoice\Invoice entity.');
        }

        $editForm = $this->createForm(new InvoiceType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Invoice\Invoice entity.
     *
     * @Route("/{id}/update", name="invoice_update")
     * @Method("POST")
     * @Template("HOfficeAdminBundle:Invoice\Invoice:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HOfficeAdminBundle:Invoice\Invoice')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Invoice\Invoice entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new InvoiceType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('invoice_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Invoice\Invoice entity.
     *
     * @Route("/{id}/delete", name="invoice_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('HOfficeAdminBundle:Invoice\Invoice')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Invoice\Invoice entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('invoice'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
