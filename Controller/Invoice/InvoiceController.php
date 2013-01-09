<?php

namespace HOffice\AdminBundle\Controller\Invoice;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use HOffice\AdminBundle\Entity\Invoice\Invoice;
use HOffice\AdminBundle\Form\Invoice\InvoiceType;
use HOffice\AdminBundle\Form\Invoice\SearchInvoiceType;
use Itc\AdminBundle\Tools\TranslitGenerator;
use Symfony\Component\Locale\Locale;
use Itc\AdminBundle\Tools\LanguageHelper;
use Itc\AdminBundle\ItcAdminBundle;
use Itc\DocumentsBundle\Entity\Pd\Pdl;
use HOffice\AdminBundle\Form\Invoice\EditInvoiceType;
use HOffice\AdminBundle\Form\Invoice\MetersEditInvoiceType;
use HOffice\AdminBundle\Entity\Service\Service;
use Doctrine\Common\Collections;
/**
 * Invoice\Invoice controller.
 *
 * @Route("/invoice")
 */
class InvoiceController extends Controller
{
    const _pdtypeId     = 1;    //invoice in pdtype
    const _metersAccId  = 2;    //meters in rest
    const _serviceS     = 0;    //сервис связанный с площадью квартиры
    const _serviceM     = 1;    //сервис связанный с показаниями счетчика
    const _serviceO     = 2;    //сервис с статической стоимостью
    const _serviceE     = 3;    //сервис электричество
    const _closed       = 2;    //статус закрытия квитанции
    const _day          = 20;   //день принятия штрафных санкций
    /**
     * Lists all Invoice\Invoice entities.
     *
     * @Route("/{coulonpage}/{page}", name="invoice",
     * requirements={"coulonpage" = "\d+","page" = "\d+"}, 
     * defaults={ "coulonpage" = "100", "page" = 1} )
     * @Template()
     */    
    public function indexAction($coulonpage = 100, $page)
    {        
        $em = $this->getDoctrine()->getManager();
        $locale =  LanguageHelper::getLocale();
        
        $date_range = array();
        
        $repo = $em->getRepository('HOfficeAdminBundle:Invoice\Invoice');
        
        $qb = $repo->createQueryBuilder('I')
                        ->select('I')
                        ->orderBy('I.id', 'DESC');    

        $paginator = $this->get('knp_paginator');
        $entities = $paginator->paginate(
            $qb,
            $this->get('request')->query->get('page', 1)/*page number*/,
            $coulonpage/*limit per page*/
        );
        $search_form = $this->createForm(new SearchInvoiceType($em, $locale));

        return array(
            'entities' => $entities,
            'search_form' => $search_form->createView(),
            'parent_id' => null,
            'locale'    => $locale,
            'coulonpage' => $coulonpage,            
            'total' => null,            
        );
    }
    /**
     * @Route("/closed_month", name="closed_month")
     */
    public function сlosedMonthAction(Request $request){
        
        $em = $this->getDoctrine()->getManager();
        $locale =  LanguageHelper::getLocale();

        $date_range = array();
        
        $first_day = date("Y")."-".(date("m")-1)."-01";
        $last_day = date("Y")."-".(date("m")-1)."-31";
        
        $repo = $em->getRepository('HOfficeAdminBundle:Invoice\Invoice');
        
        $qb = $repo->createQueryBuilder('I')
                        ->select('I')
                        ->where("I.date >= :first_day AND I.date <= :last_day")
                        ->setParameter('first_day', $first_day)
                        ->setParameter('last_day', $last_day)
                        ->orderBy('I.id', 'DESC');    
        
       $entities = $qb->getQuery()->execute();
       
       return $this->redirect($this->generateUrl('invoice'));
    }
    
    /**
     * @Route("/{coulonpage}/search", name="invoice_search",
     * requirements={"coulonpage" = "\d+"}, 
     * defaults={"coulonpage" = "100"})
     * @Template("HOfficeAdminBundle:Invoice\Invoice:index.html.twig")
     */
    public function searchAction(Request $request, $coulonpage = 100)
    {
        $locale =  LanguageHelper::getLocale();
        $em = $this->getDoctrine()->getManager();        
        
        $postData = $request->request->get('itc_documentsbundle_searchinvoicetype');
        $house_id = $postData['house_id'];
        
        $search_form = $this->createForm(new SearchInvoiceType($em, $locale, $house_id));
        $search_form->bind($request);
        $deleteForm = array();   
        $visibleForm = array();
        $changeKodForm = array();   
        $summa1 = 0 ;
        $data = $search_form->getData();
        
        $repo = $em->getRepository('HOfficeAdminBundle:Invoice\Invoice');
        
        $qb = $repo->createQueryBuilder('I')
                        ->select('I')
                        ->orderBy('I.id', 'DESC');            

        if(!is_null($data["house_id"]) || !is_null($data["user_id"]))
            $qb->innerJoin('I.contract', 'C');
        if(!is_null($data["user_id"]))
        {
            $qb->innerJoin('C.user', 'U', 
                       'WITH', 'U.id = :user')
               ->setParameter('user', $data["user_id"]);
        }
        if(!is_null($data["house_id"]))
        {
            $qb->innerJoin('C.apartment', 'A', 
                    'WITH', 'A.house = :house')
               ->setParameter('house', $data["house_id"]);
        
            if(!is_null($data["apartment_id"]))
            {
                $qb->andWhere('A.id = :apartment_id')
                   ->setParameter('apartment_id', $data["apartment_id"]);
            }
        }
        
        if(!is_null($data["id"]))
        {
            $qb->orWhere('I.id = :id')
                ->setParameter('id', $data["id"])
                ;
        }
        if(!is_null($data["serial_number"]))
        {
            $qb->orWhere('I.N = :serial_number')
                ->setParameter('serial_number', $data["serial_number"])
                ;
        }
        if(!is_null($data["month"]))
        {
            $qb->orWhere('SUBSTRING(I.date,6,7) = :month')
                ->setParameter('month', $data["month"])
                ;
        }
        if(!is_null($data["year"]))
        {
            $qb->orWhere('SUBSTRING(I.date,1,4) = :year')
                ->setParameter('year', $data["year"])
                ;
        }

        $paginator = $this->get('knp_paginator');
        
        $entities = $paginator->paginate(
            $qb,
            $this->get('request')->query->get('page', 1),
            100
        );
        foreach ($entities as $entity){
            $summa1 += $entity->getSumma1();
            $deleteForm[$entity->getId()] = $this->createDeleteForm($entity->getId())
                            ->createView();
        }
        return array(
            'entities'  => $entities,
            'locale'    => $locale,
            'parent_id' => null,
            'chmap'     => array(),
            'search_form' => $search_form->createView(),
            'delete_form' => $deleteForm,
            'coulonpage' => $coulonpage,
            'total' => array('summa1' => $summa1),
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
     * @Template("HOfficeAdminBundle:Invoice\Invoice:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entity  = new Invoice();
        $form = $this->createForm(new InvoiceType(), $entity);
        $form->bind($request);
        
        $contract=$em->getRepository("HOfficeAdminBundle:Contract\Contract")->find($entity->getContractId());
        $entity->setContract($contract);
        
        $pdtype = $em->getRepository("ItcDocumentsBundle:Pd\Pdtype")->find(self::_pdtypeId);
        $entity->setPdtype($pdtype);
        $entity->setSumma1(0);  
        if ($form->isValid()) 
        {
            $em->persist($entity);
            
            $services = $entity->getContract()->getServices();
            foreach($services as $service)
            {
                $pdline = new Pdl();
                $pdline->setPd($entity);
                $pdline->setOa1($service->getId());
                $em->persist($pdline);
                
            }
            $em->flush();
            
           return $this->redirect($this->generateUrl('invoice_edit', array('id' => $entity->getId())));
        }else{
            print_r($form->getErrorsAsString());
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
        $repo = $em->getRepository( "ItcDocumentsBundle:Pd\Rest" );
        $rests = $repo->find( self::_metersAccId , array('l1'=>$entity->getContract( )->getId()), $entity->getDate()->format('Y'), $entity->getDate()->format('m') );        
        $services = $entity->getContract()->getServices();
                
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Invoice\Invoice entity.');
        }
        if (!$services) {
            throw $this->createNotFoundException('Unable to find Invoice\Invoice entity.');
        }
        
//        $serv_form = array();       
//        $serviceM;
//        $closed;
//        foreach ($services as $service) 
//        {        
//            foreach ($entity->getPdlines() as $pdl ) 
//            {
//                if($pdl->getOa1()==$service->getId())
//                {
//                    
//                    if($service->getKod()==self::_serviceM)
//                    {
//                        $serviceM = true;
//                        foreach ($rests as $rest) {
//                            if($service->getId()==$rest->getL2())
//                            {
//                               $pdl->setSumma2($pdl->getSumma2()+$rest->getSd()); 
//                            }
//                        }
//                    }
//                    else
//                    {
//                        $serviceM = false;
//                    }
//                    
//                    
//                    
//                    $serv_form[] = $this->createForm(new MetersEditInvoiceType($serviceM, $closed), $pdl)->createView();
//                }
//            }
//        } 
        
        foreach ($services as $service) 
        {
            foreach ($entity->getPdlines() as $pdl )
            {
                if($service->getId() == $pdl->getOa1())
                {
                    echo $pdl->getOa1().'<br>';
                }
            }
        } 

        
        
        $price = $this->generatePrice($entity, $entity->getContract(),$services);
        
        $closed=$entity->getStatus()==self::_closed?true:false;
        $editForm = $this->createForm(new EditInvoiceType($closed),$entity);

        return array(
//            'serv_form'     => $serv_form,
            'serviceM'      => self::_serviceM,
            'rests'         => $rests,
            'entity'        => $entity,
            'price'         => $price,
            'services'      => $services,
            'edit_form'     => $editForm->createView(),
        );
    }
    
    private function generatePrice($Invoice, $Contract, $Services){
        $prices=array();
        $penalty;
        if(($Invoice->getDate()->format('m')!=date('m')) and (date('d')>self::_day) and ($Invoice->getStatus()!=self::_closed))
        {
            $penalty=TRUE;
        }
        else
        {
            $penalty=FALSE;
        }
        $price;
        foreach ($Services as $service) {
             $price=$penalty?$service->getPrice1():$service->getPrice(); 
             $prices[]= $this->typesOfServices($service->getKod(),$price, $Contract->getApartment()->getSAll());       
        }

        return $prices;
    }
    
    private function typesOfServices($kod,$price,$S)
    {
        switch ($kod) {
            case self::_serviceS :return $price*$S;
            case self::_serviceM :return $price;
            case self::_serviceO :return $price;
            default:
                return $price;
            }; 
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
        $editForm = $this->createForm(new EditInvoiceType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);

            $em->flush();

            return $this->redirect($this->generateUrl('invoice_edit', array('id' => $id)));
        }
        $services = $entity->getContract()->getServices();
        return array(
            //'sale' => $entity->getContract()->getSale(),
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'services'      => $services,
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
