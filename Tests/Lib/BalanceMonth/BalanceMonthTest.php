<?php

namespace HOffice\AdminBundle\Tests\Lib\BalanceMonth;
use Itc\AdminBundle\Tools\KernelAwareTest;
use HOffice\AdminBundle\Lib\Balance\BalanceService;
use HOffice\AdminBundle\Entity\Contract\Contract;
use HOffice\AdminBundle\Entity\Payment\Payment;
use HOffice\AdminBundle\Entity\Invoice\Invoice;
use Itc\DocumentsBundle\Entity\Pd\Trans;

/**
 * Description of BalanceMonthTest
 *
 * @author root
 */
class BalanceMonthTest extends KernelAwareTest
{
    private $service;
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
    /**
     * Стандартная сумма документов
     */
    const sum = 100;
    /**
     * Количество контрактов
     */
    const cnt_contract = 5;
    
    public function setUp() {
        parent::setUp();        
        for($i = 0; $i < self::cnt_contract ; $i++)
        {
        $contract = $this->createContract();
        $this->entityManager->flush();
        $contracts[] = $contract;
        $invoice = $this->createInvoice( $contract, self::sum );
        $payment = $this->createPayment();
            $payment->setInvoice( $invoice );          
        }
        $this->entityManager->flush();
        
        if ( !isset($this->y) || !isset($this->m))
            list($this->y, $this->m) = explode(",", 
               date("Y,m", mktime(0, 0, 0, date("n") + 1)));
        
        $this->service = new BalanceService( /*$contracts */);
    }
    
    public function testBalance()
    {
        $contracts = $this->entityManager
                        ->getRepository("HOfficeAdminBundle:Contract\Contract")
                        ->findAll();
        
        $this->assertEquals(self::cnt_contract, count($contracts));
        
        $invoices = $this->entityManager
                        ->getRepository("HOfficeAdminBundle:Invoice\Invoice")
                        ->findAll();
        
        $this->assertEquals(self::cnt_contract, count($invoices));
        
        $payments = $this->entityManager
                        ->getRepository("HOfficeAdminBundle:Payment\Payment")
                        ->findAll();
        
        $this->assertEquals(self::cnt_contract, count($payments));

        foreach ( $payments as $payment )
        {
            $payment->setStatus( self::pd_paid );
            $this->entityManager->persist ( $payment );
            $this->entityManager->flush();
        }       
        
        $this->service->execute();

        foreach ($invoices as $invoice )
        {   
            $this->assertEquals(self::pd_paid , $invoice->getStatus() );
        }

        $invoices = $this->entityManager
                        ->getRepository("HOfficeAdminBundle:Invoice\Invoice")
                        ->findBy( array( "status" => self::pd_new ) );
        
        $this->assertEquals(self::cnt_contract , count($invoices));         
        
    }
    /**
     * Создание контракта
     * @return \HOffice\AdminBundle\Entity\Contract\Contract
     */
    private function createContract()
    {
        $contract = new Contract();
        $contract->setUserId(1);
        $contract->setApartmentId(5);
        $contract->setKod(1);
        $contract->setRegistered(5);
        $this->entityManager->persist($contract);
        return $contract;
    }
    /**
     * Создание квитанции
     */
    private function createInvoice( $contract, $sum = self::sum )
    {
        $invoice = new Invoice();
        $invoice->setN(1);
        $invoice->setSumma1( $sum );
        $invoice->setSumma2(0);
        $invoice->setSumma3(0);
        $invoice->setStatus( self::pd_not_paid );
        $invoice->setContract( $contract );
        $this->entityManager->persist($invoice);
        $this->entityManager->flush();
        
        $trans[] = array(
                    'iaccid'  => self::rest_detail,
                    'il1'     => $contract->getId(), 
                    'il2'     => $invoice->getId(), 
                    'il3'     => NULL,
                    'summa'   => $sum );
        $trans[] = array(
                    'iaccid'  => self::rest_total,
                    'il1'     => $contract->getId(), 
                    'il2'     => NULL, 
                    'il3'     => NULL,
                    'summa'   => $sum );
        $this->createTrans($invoice, $trans );
        return $invoice;
    }
    /**
     * Создание оплаты
     */
    private function createPayment( $sum = self::sum )
    {
        $payment = new Payment();
        $payment->setN(1);
        $payment->setSumma1( $sum );
        $payment->setSumma2(0);
        $payment->setSumma3(0);        
        $payment->setStatus( self::pd_not_paid );
        $this->entityManager->persist($payment);
        return $payment;
    }

    /**
     * Проверка Общего регистра по контракту
     * @param type $contract_id ид контракта
     * @param type $expected ожидаемое значение
     */
    private function checkTotalRest( $contract_id, $expected )
    {
        $result = null;
        $repo = $this->entityManager
                        ->getRepository("ItcDocumentsBundle:Pd\Rest");
        
        $ballance =  $repo->findOne( self::rest_total, 
                array("l1" => $contract_id,
                      "l2" => NULL,
                      "l3" => NULL,
                      ), $this->y, $this->m );
        
        if (is_object($ballance))
            $result = $ballance->getSd();
        
        $this->assertEquals( $expected, $result );
        
    }
    /**
     * Проверка Детализированного регистра по квитанции
     * @param type $contract_id ид контракта
     * @param type $pdid ид квитанции
     * @param type $expected ожидаемое значение
     */
    private function checkDetailRest( $contract_id, $pdid, $expected )
    {
        $repo = $this->entityManager
                        ->getRepository("ItcDocumentsBundle:Pd\Rest");        
        
        $ballance =  $repo->findOne( self::rest_detail, 
                array("l1" => $contract_id,
                      "l2" => $pdid,
                      "l3" => NULL,
                      ), $this->y, $this->m );
        
        $this->assertEquals( $expected, $ballance->getSd() );
        
    }
    
    private function createTrans($pd, $trans)
    {        
        foreach( Trans::getTransactions( $pd , $trans ) as $entity )
        {
            $this->entityManager->persist( $entity );
        }        
    }
    
}