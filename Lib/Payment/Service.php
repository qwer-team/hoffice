<?php

namespace HOffice\AdminBundle\Lib\Payment;
use Itc\DocumentsBundle\Entity\Pd\Trans;
//use Symfony\Component\EventDispatcher\EventDispatcher;
use HOffice\AdminBundle\Event\PaymentEvent;

/**
 * Description of Service
 *
 * @author root
 */
class Service {
    
    private $payment;
    private $container;
    
    function __construct($payment = null, $container = null) {
        $this->payment = $payment;
        $this->container = $container;
    }
    function listen($event){
        echo "lololo";
    }
    public function execute()
    {
/*        $em = $this->container->get("doctrine")->getEntityManager(); 
        
        
        $repo = $em->getRepository("ItcDocumentsBundle:Pd\Trans");
        
        $invoice = $this->payment->getInvoice();
//        $invoice->getSumma1();
        
        $tr = new Trans();
        $tr->setIaccId(1);
        $tr->setOaccId(2);
        $tr->setPd($this->payment);
        $tr->setSumma($this->payment->getSumma1());
        
        
        $em->persist($tr);
        $em->flush();*/
          $this->onPaymentCreate($event);
    }
  
    public function onPaymentCreate($event)
    {
        
        $create = $event->getCreate();
        echo "wow";
        
    }

}