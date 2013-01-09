<?php

namespace HOffice\AdminBundle\Lib\Balance;
use HOffice\AdminBundle\Entity\Payment\Payment;
use HOffice\AdminBundle\Entity\Invoice\Invoice;
/**
 * Description of BalanceService
 *
 * @author root
 */
class BalanceService {
    
    private $entityManager;
    private $contacts;
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
    /**
     * Статус нового дукумента
     */
    const pd_new = 0;
    
    
    function __construct( /*$contracts = null*/ ) {        
        
        //$this->contracts = $contracts;
        $this->container = \Itc\AdminBundle\ItcAdminBundle::getContainer();
        list($this->y, $this->m) = explode(",", 
            date("Y,m", mktime(0, 0, 0, date("n") + 1)));
    }    
    
    public function execute()
    {
        echo "ata";
        $this->entityManager = $this->container->get("doctrine")->getEntityManager(); 
        
        $this->paymentNotClosedInvoices();
//        $this->recalculationInvoices();
        $this->createNewInvoices();
    }    
    public function onCreateBalanceMonth( $event )
    {
        $balance_month = new BalanceService();
        $balance_month->execute();
    }
    /**
     * Создание новых квитанций
     */
    private function createNewInvoices()
    {
        $contracts = $this->entityManager
                        ->getRepository("HOfficeAdminBundle:Contract\Contract")
                        ->findAll();
        foreach ( $contracts as $contract )
        {
            $new_invoice = new Invoice();
//                $sum = $this->calculateInvoiceSum($contract->getId());
            $sum = 100;
            $new_invoice->setN(1);
            $new_invoice->setSumma1( $sum );
            $new_invoice->setSumma2(0);
            $new_invoice->setSumma3(0);
            $new_invoice->setStatus( self::pd_new );
            $new_invoice->setContract( $contract );
            $this->entityManager->persist($new_invoice);
        }
        $this->entityManager->flush();
        //$this->enti
    }

    /**
     * Перерасчет квитанций
     */
    private function recalculationInvoice( $invoice )
    {
/*        $invoices = $this->entityManager
                         ->getRepository("HOfficeAdminBundle:Invoice\Invoice")
                         ->findBy(array('status' => 1)
                               );        */
        
    }

    /**
     * Закрытие неоплаченых квитанций
     */
    private function paymentNotClosedInvoices()
    {           
        $invoices = $this->entityManager
                         ->getRepository("HOfficeAdminBundle:Invoice\Invoice")
                         ->findBy(array('status' => 1)
                               );
        
        foreach( $invoices as $invoice )
        {echo "aha";
            $repo = $this->entityManager
                         ->getRepository("ItcDocumentsBundle:Pd\Rest");        
            
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
                $this->entityManager->persist( $payment );
            
            }
            else
            {
                $this->recalculationInvoice( $invoice );
            }
        }
        $this->entityManager->flush();
    }
    
}