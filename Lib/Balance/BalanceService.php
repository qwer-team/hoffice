<?php

namespace HOffice\AdminBundle\Lib\Balance;
use HOffice\AdminBundle\Entity\Payment\Payment;
use HOffice\AdminBundle\Entity\Invoice\Invoice;
use Itc\DocumentsBundle\Entity\Pd\Pdl;
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
    const _pdtypeId     = 1;    //invoice in pdtype
    const _serviceSall  = 0;    //сервис связанный с общей площадью квартиры
    const _serviceSh    = 4;    //сервис связанный с отапливаемой площадью квартиры
    const _serviceM     = 1;    //сервис связанный с показаниями счетчика
    const _serviceO     = 2;    //сервис с статической стоимостью
    const _serviceE     = 3;    //сервис электричество
    const _serviseShLimit = 250; //кол-во кВт для выбора тарифа
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
    
    
    function __construct() {                
        $this->container = \Itc\AdminBundle\ItcAdminBundle::getContainer();
        list($this->y, $this->m) = explode(",", 
            date("Y,m", mktime(0, 0, 0, date("n") + 1)));
    }    
    
    public function execute()
    {
        $this->entityManager = $this->container->get("doctrine")->getEntityManager(); 
        
        $this->paymentNotClosedInvoices();
        
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
            $new_invoice->setN(1);
            $sum = $this->calculateInvoiceSum( $new_invoice, $contract );
            $new_invoice->setSumma1( $sum );
            $new_invoice->setSumma2(0);
            $new_invoice->setSumma3(0);
            $new_invoice->setStatus( self::pd_new );
            $new_invoice->setContract( $contract );            
            $this->entityManager->persist( $new_invoice );
            $this->entityManager->flush();
        }
                
    }
    
    /**
     * Создание строк сервисов
     * @param type $invoice квитанция
     * @param type $contract контракт
     */
    private function calculateInvoiceSum( $invoice , $contract )
    {
        $services = $contract->getServices();
        $sum = 0; 
        foreach($services as $service)
        {
            $pdline = new Pdl();
            $pdline->setPd( $invoice );
            $pdline->setOa1( $service->getId() );
            $line_sum = 
                $this->calculateLineSumNewInvoice( $service, $contract );
            $pdline->setSumma1( $line_sum );
            $sum += $line_sum;
            $this->entityManager->persist( $pdline );            
        }
        return $sum;
    }
    
    /**
     * Рассчет суммы по сервису
     * @param type $service сервис
     * @param type $contract контракт
     * @return type сумма линии
     */
    private function calculateLineSumNewInvoice( $service, $contract )
    {
        $price = $service->getPrice();
        
        switch ( $service->getKod() ) {
            case self::_serviceSall :
                        return $price * $contract->getApartment()->getSAll();
            case self::_serviceSh :
                        return $price * $contract->getApartment()->getSLive();
            case self::_serviceM :return 0;
            case self::_serviceE :return 0;
            case self::_serviceO :return $price;
            default:
                return $price;
            }; 
    }
    /**
     * Рассчет суммы по сервису
     * @param type $service сервис
     * @param type $contract контракт
     * @return type сумма линии
     */
    private function calculateLineSumUnpaidInvoice( $line, $service, $contract )
    {
        if($service->getKod() == self::_serviceE &&
                $line->getSumma2() < self::_serviseShLimit)
            $price = $service->getPrice();        
        else        
            $price = $service->getPrice1();
        
        switch ( $service->getKod() ) {
            case self::_serviceSall :
                        return $price * $contract->getApartment()->getSAll();
            case self::_serviceSh :
                        return $price * $contract->getApartment()->getSLive();
            case self::_serviceM :return $price * $line->getSumma2();
            case self::_serviceE :return $price * $line->getSumma2();
            case self::_serviceO :return $price;
            default:
                return $price;
            }; 
    }

    /**
     * Перерасчет квитанций
     */
    private function recalculationInvoice( $invoice )
    {
        $lines = $invoice->getPdlines();
        $services = $invoice->getContract()->getServices();
        $invoice->setStatus(self::pd_new);
        $this->entityManager->persist($invoice);
        $invoice_sum = 0;
        
        foreach ($lines as $line)
        {
            $service_id = $line->getOa1();
            foreach($services as $service)
            {
                if ($service_id == $service->getId())
                {
                $line_sum = $this->calculateLineSumUnpaidInvoice($line, 
                                            $service, $invoice->getContract());
                $line->setSumma1($line_sum);
                $invoice_sum += $line_sum;
                }
            }            
        }
        $invoice->setSumma1($invoice_sum);
        $invoice->setStatus(self::pd_not_paid);
        $this->entityManager->persist($invoice);
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
        {
            $repo = $this->entityManager
                         ->getRepository("ItcDocumentsBundle:Pd\Rest");        
            
            $rest_contract = $repo->findOne( self::rest_total , 
                                array('l1' => $invoice->getContract()->getId(),
                                      'l2' => NULL,
                                      'l3' => NULL),
                                      $this->y, $this->m );
            
            if (is_object($rest_contract) && $rest_contract->getSd() < 0)
            {
                $rest_invoice = $repo->findOne( self::rest_detail , array(
                                      'l1' => $invoice->getContract()->getId(),
                                      'l2' => $invoice->getId(),
                                      'l3' => NULL),
                                      $this->y, $this->m );
                
                if (abs($rest_contract->getSd()) >= $rest_invoice->getSd())
                {            
                    $payment_sum = $rest_invoice->getSd();
                }
                else
                {
                    $payment_sum = abs($rest_contract->getSd());
                }
                
                $payment = new Payment();
                $payment->setN($invoice->getId());
                $payment->setInvoice($invoice);
                $payment->setSumma1($payment_sum);
                $payment->setStatus(self::pd_not_paid);
                $this->entityManager->persist( $payment );
                $this->entityManager->flush();
                $payment->setStatus(self::pd_paid);
                $this->entityManager->persist( $payment );
            }
            if ($invoice->getStatus() == self::pd_not_paid)
            {   
                $this->recalculationInvoice( $invoice );
            }
        }
        $this->entityManager->flush();
    }
    
}