<?php

namespace HOffice\AdminBundle\Lib\Payment;
use Itc\DocumentsBundle\Entity\Pd\Trans;
//use Symfony\Component\EventDispatcher\EventDispatcher;
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


    function __construct($payment = null, $container = null) {
        $this->payment = $payment;
        $this->container = \Itc\AdminBundle\ItcAdminBundle::getContainer();//$container;
    }    

    public function execute()
    {
        if ($this->payment->getStatus() == 3 )
        {
            $this->createPaymentTrans();
        }
        else
        {
            $this->deletePaymentTrans();        
        }
    }
    
    protected function createPaymentTrans(){
        
        $em = $this->container->get("doctrine")->getEntityManager();                       
        
        $this->invoice = $this->payment->getInvoice();
        $this->contract = $this->invoice->getContract();        
        $paid_invoices = array();
        $invoices = array();
        
        if ($payment_sum >= $invoice_sum)
        {
            $this->invoice->setStatus(3);
        }
        
        if ($payment_sum > $invoice_sum)
        {
            
            $this->addTrans( array('oaccid'  => 3,
                                   'summa' => $invoice_sum ) );
            $this->addTrans( array('oaccid'  => 1,
                                   'ol2' => $this->invoice->getId(),
                                   'summa' => $invoice_sum
                                   ) );
            
            list($entries, $paid_invoices) = $this->expandRestSum($invoice_sum, 
                                                   $payment_sum - $invoice_sum);
            
            foreach ($entries as $entry)
            {
                $this->addTrans( $entry );                
            }
            print_r($paid_invoices);
            if (count($paid_invoices) > 0) {
                
                $invoices = 
                    $em->getRepository( 'HOfficeAdminBundle:Invoice\Invoice' )
                       ->findBy( array( 'id' => $paid_invoices ) );
                
                foreach($invoices as $invoice){
                    $invoice->setStatus(3);
                    $em->persist( $invoice );
                }   
                echo "invoice=".$invoice->getId()."<br/>";
            }
            print_r($this->trans);
        }
        else 
        {
            
            $this->addTrans( array('oaccid'  => 3,
                                   'summa' => $payment_sum ) );
            $this->addTrans( array('oaccid'  => 1,
                                   'ol2' => $this->invoice->getId(),
                                   'summa' => $payment_sum
                                   ) );
            
        }
//        print_r($this->getTrans());
        foreach( Trans::getTransactions( $this->payment, $this->getTrans() )
                        as $entity ){
                                $em->persist( $entity );
                                }
        $em->flush();        
    }

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
        print_r($invoices);
        $cancelled_invoices = 
                    $em->getRepository( 'HOfficeAdminBundle:Invoice\Invoice' )
                       ->findBy( array( 'id' => $invoices ) );
        
        foreach($cancelled_invoices as $invoice)
        {
            $invoice->setStatus(2);
            $em->persist( $invoice );
        }
        
        $em->flush();
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
        $em = $this->container->get("doctrine")->getEntityManager(); 
        
        $create = $event->getCreate();
        
        $this->service = new Service($create);
        $this->service->execute();
        
    }
    
    private function getRestData($Iacc, $lvl = array()){
        
        $em = $this->container->get("doctrine")->getEntityManager(); 
        
        $repo = $em->getRepository( "ItcDocumentsBundle:Pd\Rest" );

        $y = date("m") == 12 ? date("Y") + 1 : date("Y");
        $m = date("m") == 12 ? 1 : date("m") + 1;
        
        $restPd = $repo->findOne( $Iacc, $lvl, $y, $m );
        
        if (is_object($restPd))
            return $restPd->getSd();
        
        return 0;
        
    }
    
    private function expandRestSum( $sum, $difference )
    {
        $em = $this->container->get("doctrine")->getEntityManager();
        $y = date("m") == 12 ? date("Y") + 1 : date("Y");
        $m = date("m") == 12 ? 1 : date("m") + 1;
        
        $this->balance =  $this->getRestData(3, 
            array("l1" => $this->contract->getId(),
                  "l2" => NULL,
                  "l3" => NULL,
                 ));
        echo '<br />sum ='.$sum.", dif =".$difference.'<br />';
        echo $this->balance."---<br/>";
        $this->balance -= $sum;
        echo $this->balance."---";
        $trans = array();
        $paid_invoices = array();
        
        if ($this->balance > 0)
        {
            
            $repo = $em->getRepository("HOfficeAdminBundle:Invoice\Invoice");
            
            $qb = $repo->createQueryBuilder('I')
                        ->select('I')
                        ->where('I.status = 2')
                        ->andWhere('I.id != :id')
                        ->setParameter('id', $this->invoice->getId())
                        ->orderBy('I.dtcor', 'DESC');
            
            $invoices = $qb->getQuery()->execute();
            foreach ($invoices as $invoice)
            {
                if ($difference > 0){
                $repo = $em->getRepository("ItcDocumentsBundle:Pd\Rest");        
                $restPd = $repo->findOne( 1 , 
                            array('l1' => $this->contract->getId(),
                                  'l2' => $invoice->getId(),
                                  'l3' => NULL),
                                  $y, $m );
                $invoice_sum = $restPd->getSd() > $difference ? 
                            $difference : $restPd->getSd();
                echo 'sum='.$difference.'inv_sum='.$invoice_sum.'restPd->getSd()'.$restPd->getSd().'<br/>';
                $difference -= $invoice_sum;
                echo 'sum='.$difference.'inv_sum='.$invoice_sum.'<br/>';
                
                if ($restPd->getSd() == $invoice_sum){
                    $paid_invoices[] = $invoice->getId();
                }
                $trans[] = array('oaccid'  => 3,
                                 'summa' => $invoice_sum );
                $trans[] = array('oaccid'  => 1,
                                 'ol2' => $invoice->getId(),
                                 'summa' => $invoice_sum );
                }
            }
                
        }
        $trans[] = array('oaccid'  => 3, 'summa' => $difference );
     //  print_r($trans);
        return array($trans, $paid_invoices);
    }

}