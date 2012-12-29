<?php

namespace HOffice\AdminBundle\Lib\Payment;
use Itc\DocumentsBundle\Entity\Pd\Trans;
use HOffice\AdminBundle\Event\PaymentEvent;
use Itc\DocumentsBundle\Entity\Pd\RestRepository;

/**
 * Description of Service
 *
 * @author root
 */
class Service {
    
    private $service;
    private $payment;
    private $container;
    private $trans;
    private $contract;
    private $invoice;
    private $balance;
    private $m;
    private $y;
    /**
     * Регистр взаиморасчёта с детализацией по документу
     */
    const rest_detail = 1; 
    /**
     * Регистр взаиморасчёта общий
     */
    const rest_total = 3;
    /**
     * Статус оплаченого документа
     */
    const pd_paid = 2;
    /**
     * Статус неоплаченного документа
     */
    const pd_not_paid = 1;
    

    function __construct($payment = null, $container = null) {
        $this->payment = $payment;
        $this->container = \Itc\AdminBundle\ItcAdminBundle::getContainer();//$container;
        list($this->y, $this->m) = explode(",", 
            date("Y,m", mktime(0, 0, 0, date("n") + 1)));
    }    

    public function execute()
    {        
        $this->invoice = $this->payment->getInvoice();
        $this->contract = $this->invoice->getContract();            
        
        if ($this->payment->getStatus() == self::pd_paid )
        {
            $this->createPaymentTrans();
        }
        else
        {
            $this->deletePaymentTrans();        
        }
    }
    /**
     * Проведение оплаты 
     */    
    protected function createPaymentTrans()
    {        
        $em = $this->container->get("doctrine")->getEntityManager();                       
        $paid_invoices = array();
        $invoices = array();
        
        $repo = $em->getRepository("ItcDocumentsBundle:Pd\Rest");        
        $rest_invoice = $repo->findOne( self::rest_detail , 
            array('l1' => $this->contract->getId(),
                  'l2' => $this->invoice->getId(),
                  'l3' => NULL),
                  $this->y, $this->m );
        
        $rest_invoice = is_object($rest_invoice) ? $rest_invoice->getSd() : 0 ;
        $invoice_sum = $rest_invoice > 0 ? $rest_invoice : 0 ;
        $payment_sum = $this->payment->getSumma1();
        
        if ($payment_sum >= $invoice_sum)
        {
            $this->invoice->setStatus(self::pd_paid);
        }
        
        if ($payment_sum > $invoice_sum)
        {
            $this->createTransForPayment( $this->invoice->getId(), $invoice_sum);
            
            list($entries, $paid_invoices) = $this->expandRestSum($invoice_sum, 
                                                   $payment_sum - $invoice_sum);
            
            foreach ($entries as $entry)
            {
                $this->addTrans( $entry );                
            }
            
            if (count($paid_invoices) > 0) {
                
                $invoices = 
                    $em->getRepository( 'HOfficeAdminBundle:Invoice\Invoice' )
                       ->findBy( array( 'id' => $paid_invoices ) );
                
                foreach($invoices as $invoice){
                    $invoice->setStatus(self::pd_paid);
                    $em->persist( $invoice );
                }   
                
            }
        }
        else 
        {
            $this->createTransForPayment( $this->invoice->getId(), $payment_sum);            
        }
        
        foreach( Trans::getTransactions( $this->payment, $this->getTrans() )
                                                                    as $entity )
        {
            $em->persist( $entity );
        }
        
        $em->flush();        
    }
    /**
     * Отмена оплаты
     */
    protected function deletePaymentTrans(){
        
        $em = $this->container->get("doctrine")->getEntityManager();                       
        $entities = $this->payment->getTransactions();
        $invoices = array();
        $pu = new \Itc\DocumentsBundle\Lib\PostUpdate();
        
        foreach($entities as $entity)
        {
            if( !is_null($entity->getOL2()) )
                    $invoices[] = $entity->getOL2();                
        }
        
        $pu->removeTransOnChangeStatusPd( $this->payment );
        
        $cancelled_invoices = 
                    $em->getRepository( 'HOfficeAdminBundle:Invoice\Invoice' )
                       ->findBy( array( 'id' => $invoices ) );
        
        foreach($cancelled_invoices as $invoice)
        {
            $invoice->setStatus(self::pd_not_paid);
            $em->persist( $invoice );
        }
        
        $em->flush();
    }
    /**
     * Создание проводок для оплаты
     * @param type $sum сумма проводок
     * @param type $pdid ид квитанции
     */
    protected function createTransForPayment( $pdid, $sum )
    {
        $this->addTrans( array('oaccid' => self::rest_total ,
                               'summa' => $sum 
                               ) );
        $this->addTrans( array('oaccid'  => self::rest_detail ,
                               'ol2' => $pdid,
                               'summa' => $sum
                               ) );        
    }

    protected function addTrans( array $trans = NULL ){
        
        $arr = array(
                    'iaccid'  => NULL,
                    'il1'     => NULL, 
                    'il2'     => NULL, 
                    'il3'     => NULL,
                    'oaccid'  => NULL,
                    'ol1'     => $this->contract->getId(), 
                    'ol2'     => NULL, 
                    'ol3'     => NULL,
                    'summa'   => NULL,
                    ) ;
        
        if(count($trans) == 0)
        {
            $this->trans[] = $arr;
        }
        else
        {
            $this->trans[] = array_merge ($arr, $trans);
        }
        
        return $this;
    }

    protected function getTrans(){

        return $this->trans;

    }
  
    public function onPaymentUpdateTrans($event)
    {
        $create = $event->getCreate();
        
        $this->service = new Service($create);
        $this->service->execute();
        
    }
    
    private function getRestData($Iacc, $lvl = array())
    {
        
        $em = $this->container->get("doctrine")->getEntityManager(); 
        
        $repo = $em->getRepository( "ItcDocumentsBundle:Pd\Rest" );

        $restPd = $repo->findOne( $Iacc, $lvl, $this->y, $this->m );
        
        if (is_object($restPd))
        {
            return $restPd->getSd();
        }
        return 0;
        
    }
    /**
     * 
     * @param type $sum - сумма оплаченой квитанции
     * @param type $difference - переплата по квитанции
     * @return type
     */
    private function expandRestSum( $sum, $difference )
    {
        $em = $this->container->get("doctrine")->getEntityManager();
        $this->balance =  $this->getRestData(self::rest_total, 
            array("l1" => $this->contract->getId(),
                  "l2" => NULL,
                  "l3" => NULL,
                 ));
        
        $this->balance -= $sum;
        $trans = array();
        $paid_invoices = array();
        
        if ($this->balance > 0)
        {
            
            $repo = $em->getRepository("HOfficeAdminBundle:Invoice\Invoice");
            
            $qb = $repo->createQueryBuilder('I')
                        ->select('I')
                        ->where('I.status = 1')
                        ->andWhere('I.id != :id')
                        ->setParameter('id', $this->invoice->getId())
                        ->orderBy('I.dtcor', 'ASC');
            
            $invoices = $qb->getQuery()->execute();

            foreach ($invoices as $invoice)
            {
                if ($difference > 0){
                    
                $repo = $em->getRepository("ItcDocumentsBundle:Pd\Rest");        
                $restPd = $repo->findOne( self::rest_detail , 
                            array('l1' => $this->contract->getId(),
                                  'l2' => $invoice->getId(),
                                  'l3' => NULL),
                                  $this->y, $this->m );

                $invoice_sum = $restPd->getSd() > $difference ? 
                            $difference : $restPd->getSd();
                $difference -= $invoice_sum;
                
                if ($restPd->getSd() == $invoice_sum){
                    $paid_invoices[] = $invoice->getId();
                }
                $this->createTransForPayment( $invoice->getId(), $invoice_sum );  
                
                }
            }
                
        }
        
        if ($difference > 0)
            $trans[] = array('oaccid'  => self::rest_total,
                             'summa' => $difference );
        
        return array($trans, $paid_invoices);
    }

}