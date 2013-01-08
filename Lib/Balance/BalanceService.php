<?php

namespace HOffice\AdminBundle\Lib\Balance;
use HOffice\AdminBundle\Entity\Payment\Payment;

/**
 * Description of BalanceService
 *
 * @author root
 */
class BalanceService {
    
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
    
    function __construct( $container = null ) {
        
        $this->container = \Itc\AdminBundle\ItcAdminBundle::getContainer();
        list($this->y, $this->m) = explode(",", 
            date("Y,m", mktime(0, 0, 0, date("n") + 1)));
    }    
    
    public function execute()
    {
        $em = $this->container->get("doctrine")->getEntityManager();                       

        echo "eee";
    }

    public function onCreateBalanceMonth( $event )
    {
        $balance_month = new BalanceService();
        $balance_month->execute();
    }
    
    private function paymentNotClosedInvoices()
    {
        $invoices = $em->getRepository("HOfficeAdminBundle:Invoice\Invoice")
                       ->findBy(array('status' => 1)
                               );
        
        foreach( $invoices as $invoice )
        {
            $repo = $em->getRepository("ItcDocumentsBundle:Pd\Rest");        
            
            $rest_contract = $repo->findOne( self::rest_detail , 
                                array('l1' => $invoice->getContract()->getId(),
                                      'l2' => NULL,
                                      'l3' => NULL),
                                      $this->y, $this->m );
            
            if ($rest_contract->getSd() < 0)
            {            
                $rest_invoice = $repo->findOne( self::rest_detail , 
                                    array('l1' => $invoice->getContract()->getId(),
                                          'l2' => $invoice->getId(),
                                          'l3' => NULL),
                                          $this->y, $this->m );

                $payment_sum = 
                        abs($rest_contract->getSd()) >= $rest_invoice->getSd() ?
                        $rest_invoice->getSd() : $rest_contract->getSd();
                
                $payment = new Payment();
                $payment->setInvoice($invoice);
                $payment->setSumma1($payment_sum);
                $payment->setStatus(self::pd_paid);
                $em->persist( $payment );
            
            }
        }
        $em->flush();
    }
    
}