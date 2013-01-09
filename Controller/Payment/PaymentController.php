<?php

namespace HOffice\AdminBundle\Controller\Payment;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use HOffice\AdminBundle\Entity\Payment\Payment;
use HOffice\AdminBundle\Form\Payment\PaymentType;
use HOffice\AdminBundle\Form\Payment\SearchPaymentType;

use HOffice\AdminBundle\Helper\ControllerHelper;

use Itc\AdminBundle\Tools\LanguageHelper;
/**
 * Payment\Payment controller.
 *
 * @Route("/payment")
 */
class PaymentController extends ControllerHelper {
    
    private $payment = "HOfficeAdminBundle:Payment\Payment";

    /**
     * Lists all Payment\Payment entities.
     *
     * @Route(
     *  "/{coulonpage}/{page}", name="payment",
     *      requirements={"coulonpage" = "\d+", "page" = "\d+"}, 
     *      defaults={ "coulonpage"="10", "page"=1 } 
     * )
     * @Template()
     */
    public function indexAction( $coulonpage = 10, $page ) {

        $em = $this->getDoctrine()->getManager();

        $rest = $em->getRepository('Itc\DocumentsBundle\Entity\Pd\Rest')
                   ->find(1, array( "l1" => 1 ), "2012", "1:10" );

        $select = "SUM( P.summa1 ) AS summa1, 
                   SUM( P.summa2 ) AS summa2, 
                   SUM( P.summa3 ) AS summa3";

        $summa  = $this->getQbAllJoins($select)
                       ->getQuery()
                       ->getOneOrNullResult();

        $search_form = $this->createForm( new SearchPaymentType( ) );

        
        $page     = $this->get('request')->query->get( 'page', $page );
        $entities = $this->get('knp_paginator')
                         ->paginate( $this->getQbAllJoins(), $page, $coulonpage );

        return array(

            'summa'       => $summa,
            'entities'    => $entities,
            'locale'      => LanguageHelper::getLocale(),
            'parent_id'   => null,
            'chmap'       => array(),
            'search_form' => $search_form->createView(),
            'delete_form' => $this->getDeleteForm( $entities ),
            'coulonpage'  => $coulonpage,

        );

    }

    /**
     * @Route(
     *      "/", name="payment_index"
     * )
     * @Template()
     */
    public function index1Action() {

        return $this->redirect( $this->generateUrl( 'payment', array() ) );

    }

    protected function getQbAllJoins( $select = NULL ){

        $select = ( $select === NULL ) ? "P, C, U": $select;
        
        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository( $this->payment )
                 ->createQueryBuilder( "P" )
                 ->select( $select )
                 ->innerJoin( 'P.contract', 'C')
                 ->innerJoin( 'C.user', 'U')
                 ->innerJoin( 'C.apartment', 'A');

        return $qb;

    }

    /**
     * @Route("/{coulonpage}/search", name="payment_search",
     * requirements={"coulonpage" = "\d+"}, 
     * defaults={"coulonpage" = "100"})
     * @Template("HOfficeAdminBundle:Payment\Payment:index.html.twig")
     */
    public function searchAction( Request $request, $coulonpage = 100 ){

        $postData = $request->request->get('itc_documentsbundle_searchpaymenttype');
        $house_id = $postData['house_id'];
        $search_form = $this->createForm( new SearchPaymentType( $house_id ) );

        $data = $search_form->bind( $request )
                            ->getData();

        $qb = $this->getSearchQuery( NULL, $data );

        if( empty( $qb ) ){
            
            return $this->redirect( $this->generateUrl( 'payment', array() ) );

        }

        $page     = $this->get('request')->query->get('page', 1);
        $entities = $this->get('knp_paginator')
                         ->paginate( $qb, $page, $coulonpage );

        $select = "SUM( P.summa1 ) AS summa1, 
                   SUM( P.summa2 ) AS summa2, 
                   SUM( P.summa3 ) AS summa3";
        $summa = $this->getSearchQuery( $select, $data )
                      ->getQuery()
                      ->getOneOrNullResult();

        return array(

            'summa'       => $summa,
            'entities'    => $entities,
            'locale'      => LanguageHelper::getLocale(),
            'parent_id'   => null,
            'chmap'       => array(),
            'search_form' => $search_form->createView(),
            'delete_form' => $this->getDeleteForm( $entities ),
            'coulonpage'  => $coulonpage,

        );

    }
    
    function getSearchQuery( $select, $data ){

        $this->resetParam();

        $qb = $this->getQbAllJoins( $select );

        $f[] = array( "n"=>'text',"f"=>array("id","N","summa1", "summa2", "summa3"), "values" => array( "OR", "=", "P" ) );
        $f[] = array( "n"=>'user',"f"=>array("user"), "values" => array( "AND", "=", "C" ) );
        $f[] = array( "n"=>'house_id',"f"=>array("house"), "values" => array( "AND", "=", "A" ) );
        $f[] = array( "n"=>'apartment_id',"f"=>array("apartment"), "values" => array( "AND", "=", "C" ) );

        foreach( $f as $k => $v ){

            $name = $data[$v['n']];

            if( null !== $name ){

                foreach( $v['f'] as $field ){
                    $parameters[$field] = $name;
                }

                $qb = $this->searchHelper( $parameters, $this->payment, $qb, $v['values'] );
                unset( $parameters );
            }
        }
        
        return $qb = $this->getDateQb( $this->payment, $data, $qb );
    }

    /**
     * Finds and displays a Payment\Payment entity.
     *
     * @Route("/{id}/show", name="payment_show")
     * @Template()
     */
    public function showAction( $id )
    {
        return $this->redirect(
                $this->generateUrl( 'payment_edit', array( 'id' => $id )
                )
        );
    }

    /**
     * Displays a form to create a new Payment\Payment entity.
     *
     * @Route("/new", name="payment_new")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Payment();
        $form   = $this->createForm(new PaymentType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a new Payment\Payment entity.
     *
     * @Route("/create", name="payment_create")
     * @Method("POST")
     * @Template("HOfficeAdminBundle:Payment\Payment:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new Payment();
        $form = $this->createForm(new PaymentType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $securityContext = $this->container->get('security.context');
            $user= $securityContext->getToken()->getUser();
//            $entity->set $user->getId();

            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('payment_edit', 
                    array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Payment\Payment entity.
     *
     * @Route("/{id}/edit", name="payment_edit")
     * @Template()
     */
    public function editAction( $id )
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HOfficeAdminBundle:Payment\Payment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Payment\Payment entity.');
        }

        $editForm = $this->createForm(new PaymentType(), $entity);
        $deleteForm = $this->createDeleteForm($id);
        
        $contract  = $entity->getContract();
        $user      = $contract->getUser();
        $apartment = $contract->getApartment();
        $house     = $apartment->getHouse();

        $repo = $em->getRepository( "ItcDocumentsBundle:Pd\Rest" );

        $y = date("m") == 12 ? date("Y") + 1 : date("Y");
        $m = date("m") == 12 ? 1 : date("m") + 1;
        
        return array(

            'entity'        => $entity,
            'user'          => $user,
            'apartment'     => $apartment,
            'house'         => $house,
            'edit_form'     => $editForm->createView(),
            'approved_form' => $this->creatApprovedForm( $entity )->createView(),
            'reversal_form' => $this->creatApprovedForm( $entity )->createView(),
            'delete_form'   => $deleteForm->createView(),

        );

    }

    /**
     * 
     * @param type $entity
     * @return type
     */
    private function creatApprovedForm( $entity ){

        return  $this->createFormBuilder( $entity )
                    ->add( 'status', 'hidden' )
                    ->getForm();

    }
    
    /**
     * Edits an existing Menu entity.
     *
     * @Route("/{id}/payment_approved", name="payment_approved")
     * @Method("POST")
     * @Template("HOfficeAdminBundle:Payment\Payment:edit.html.twig")
     */
    public function paymentApproved( Request $request, $id ){
        
        $em = $this->getDoctrine()->getManager();
        $rArray = array( "id" => $id );
        $entity = $em->getRepository( $this->payment )
                ->createQueryBuilder('P')
                ->select('P, I')
                ->innerJoin('P.invoice', 'I')
                ->where("P.id = :id")
                ->setParameter("id", $id)
                ->getQuery()
                ->getOneOrNullResult();
        
        $invoice_status = $entity->getInvoice()->getStatus();
        if ($invoice_status == 1)
            return $this->redirect( $this->generateUrl( 'payment_edit', $rArray ) );
        
        return $this->paymentChangeStatus( $request, $id, 2 );
    }
    /**
     * Edits an existing Menu entity.
     *
     * @Route("/{id}/payment_reversal", name="payment_reversal")
     * @Method("POST")
     * @Template("HOfficeAdminBundle:Payment\Payment:edit.html.twig")
     */
    public function paymentRolled( Request $request, $id ){

        return $this->paymentChangeStatus( $request, $id, 1 );
    }

    private function paymentChangeStatus( Request $request, $id, $status ){

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository( $this->payment )->find( $id );

        $form = $this->creatApprovedForm( $entity );
        $form->bind( $request );
        $data = $form->getData();
        $data->setStatus( $status );
        
        if ( $form->isValid() ) {

            $em->flush();

        } else {

            print_r( $form->getErrors() );

        }
        
        $rArray = array( "id" => $id );

        return $this->redirect( $this->generateUrl( 'payment_edit', $rArray ) );

    }

    /**
     * Edits an existing Payment\Payment entity.
     *
     * @Route("/{id}/update", name="payment_update")
     * @Method("POST")
     * @Template("HOfficeAdminBundle:Payment\Payment:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('HOfficeAdminBundle:Payment\Payment')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Payment\Payment entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm( new PaymentType(), $entity );
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('payment_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Payment\Payment entity.
     *
     * @Route("/{id}/delete", name="payment_delete")
     * @Method("POST")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('HOfficeAdminBundle:Payment\Payment')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Payment\Payment entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('payment'));
    }

    /**
     * Edits an existing Menu entity.
     * BaseController
     * 
     * @Route("/{id}/payment_delete_ajax", name="payment_delete_ajax")
     * defaults={"_format" = "json"})
     * @Method("POST")
     * @Template("ItcAdminBundle:Menu:deleteMenu.json.twig")
     */
    public function deletePaymentAjaxAction( Request $request, $id )
    {
        $this->deleteEntityById( $id,  $this->payment, $request );
    }
    
}
