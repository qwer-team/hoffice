<?php

namespace HOffice\AdminBundle\Tests\Lib\Payment;
use Itc\AdminBundle\Tools\KernelAwareTest;
use HOffice\AdminBundle\Entity\Payment\Payment;
use HOffice\AdminBundle\Entity\Invoice\Invoice;
use HOffice\AdminBundle\Entity\Contract\Contract;
use HOffice\AdminBundle\Lib\Payment\PaymentService;
use Itc\DocumentsBundle\Entity\Pd\Trans;

class ServiceTest extends KernelAwareTest 
{
    private $service;
    private $payment;
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

    public function setUp() {
        parent::setUp();
        $contract = new Contract();
        $contract->setUserId(1);
        $contract->setApartmentId(5);
        $contract->setKod(1);
        $contract->setRegistered(5);
        $this->entityManager->persist($contract);
        
        $invoice = new Invoice();
        $invoice->setN(1);
        $invoice->setSumma1(100);
        $invoice->setSumma2(0);
        $invoice->setSumma3(0);
        $invoice->setStatus(1);
        $invoice->setContract($contract);
        $this->entityManager->persist($invoice);
        $this->entityManager->flush();
        
        $trans[] = array(
                    'iaccid'  => self::rest_detail,
                    'il1'     => $contract->getId(), 
                    'il2'     => $invoice->getId(), 
                    'il3'     => NULL,
                    'summa'   => 100 );
        $trans[] = array(
                    'iaccid'  => self::rest_total,
                    'il1'     => $contract->getId(), 
                    'il2'     => NULL, 
                    'il3'     => NULL,
                    'summa'   => 100 );        
        $this->createTrans($invoice, $trans );        
        $this->entityManager->flush();
        
        $this->payment = new Payment();
        $this->payment->setN(1);
        $this->payment->setStatus(2);
        $this->payment->setInvoice($invoice);
        
        $this->service = new PaymentService($this->payment);

   //     $param = \Itc\AdminBundle\ItcAdminBundle::getContainer();
        
        if ( !isset($this->y) || !isset($this->m))
            list($this->y, $this->m) = explode(",", 
               date("Y,m", mktime(0, 0, 0, date("n") + 1)));
    }
    /*
     * Проверка когда сумма оплаты == сумма квитанции
     */
    public function testSummEqual()
    {
        $this->paymentSummas(100, 0, 0);       

        $contract_id = $this->payment->getInvoice()->getContract()->getId();     
     
        $this->checkDetailRest($contract_id, 
                               $this->payment->getInvoice()->getId(),
                               $this->payment->getInvoice()->getSumma1() );
        
        $this->checkTotalRest($contract_id, 
                              $this->payment->getInvoice()->getSumma1() );
        
        $this->service->execute();
        
        $this->assertEquals( $this->payment->getInvoice()->getStatus(),
                                                                self::pd_paid);
        $this->assertEquals( $this->payment->getStatus(), self::pd_paid);
        
        $total = $this->payment->getInvoice()->getSumma1() - 
                $this->payment->getSumma1();
        
        $this->checkDetailRest($contract_id, 
                               $this->payment->getInvoice()->getId(),
                               $total );
        
        $this->checkTotalRest($contract_id, $total );
                
    }
    /*
     * Проверка когда сумма оплаты < сумма квитанции
     */
    public function testSummLess()
    {
        $this->paymentSummas(25, 0, 0);
 
        $contract_id = $this->payment->getInvoice()->getContract()->getId();     
        
        $this->checkDetailRest($contract_id, 
                               $this->payment->getInvoice()->getId(),
                               $this->payment->getInvoice()->getSumma1() );
        
        $this->checkTotalRest($contract_id, 
                              $this->payment->getInvoice()->getSumma1() );
        
        $this->service->execute();
        
        $this->assertEquals( $this->payment->getInvoice()->getStatus(), 
                                                            self::pd_not_paid);
        $this->assertEquals( $this->payment->getStatus(), self::pd_paid);
        
        $total = $this->payment->getInvoice()->getSumma1() - 
                $this->payment->getSumma1();
        
        $this->checkDetailRest($contract_id, 
                               $this->payment->getInvoice()->getId(),
                               $total );
        
        $this->checkTotalRest($contract_id, $total );
        
    }
    /*
     * Проверка когда сумма оплаты > сумма квитанции
     */
    public function testSummMore()
    {
        $this->paymentSummas(200, 0, 0);

        $contract_id = $this->payment->getInvoice()->getContract()->getId();     
        
        $this->checkDetailRest($contract_id, 
                               $this->payment->getInvoice()->getId(),
                               $this->payment->getInvoice()->getSumma1() );
        
        $this->checkTotalRest($contract_id, 
                              $this->payment->getInvoice()->getSumma1() );
        
        $this->service->execute();
        
        $this->assertEquals( $this->payment->getInvoice()->getStatus(),
                                                                self::pd_paid);
        $this->assertEquals( $this->payment->getStatus(), self::pd_paid);
        
        $total = $this->payment->getInvoice()->getSumma1() - 
                $this->payment->getSumma1();
        
        $this->checkDetailRest($contract_id, 
                               $this->payment->getInvoice()->getId(),
                               0 );
        
        $this->checkTotalRest($contract_id, $total );
        
    }
    /*
     * Проверка когда сумма оплаты > сумма квитанции
     */
    public function testSummMoreInvoice()
    {
        $this->paymentSummas(300, 0, 0);
        
        $second_invoice = $this->createInvoice(
                            $this->payment->getInvoice()->getContract(), 150);
        
        $contract_id = $this->payment->getInvoice()->getContract()->getId();     
        
        $total_invoices = $second_invoice->getSumma1() + 
                        $this->payment->getInvoice()->getSumma1();
        
        $this->checkDetailRest($contract_id, 
                               $this->payment->getInvoice()->getId(),
                               $this->payment->getInvoice()->getSumma1() );

        $this->checkDetailRest($contract_id, 
                               $second_invoice->getId(),
                               $second_invoice->getSumma1() );
        
        $this->checkTotalRest($contract_id, 
                              $total_invoices );
        
        $this->service->execute();
        
        $this->assertEquals( $this->payment->getInvoice()->getStatus(),
                                                                self::pd_paid);
        $this->assertEquals( $second_invoice->getStatus(), self::pd_paid);
        $this->assertEquals( $this->payment->getStatus(), self::pd_paid);
        
        $total = $total_invoices - $this->payment->getSumma1();
        
        $this->checkDetailRest($contract_id, 
                               $this->payment->getInvoice()->getId(),
                               0 );

        $this->checkDetailRest($contract_id, 
                               $second_invoice->getId(),
                               0 );
        
        $this->checkTotalRest($contract_id, $total );
        
    }
    
    /**
     * Проверка Общего регистра по контракту
     * @param type $contract_id ид контракта
     * @param type $expected ожидаемое значение
     */
    private function checkTotalRest( $contract_id, $expected )
    {
        $repo = $this->entityManager
                        ->getRepository("ItcDocumentsBundle:Pd\Rest");
        
        $ballance =  $repo->findOne( self::rest_total, 
                array("l1" => $contract_id,
                      "l2" => NULL,
                      "l3" => NULL,
                      ), $this->y, $this->m );
        
        $this->assertEquals( $expected, $ballance->getSd() );
        
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

    private function paymentSummas($summa1, $summa2, $summa3)
    {
        $this->payment->setSumma1($summa1);
        $this->payment->setSumma2($summa2);
        $this->payment->setSumma3($summa3);
        $this->entityManager->persist($this->payment);
        $this->entityManager->flush($this->payment);
    }
    
    private function createTrans($pd, $trans)
    {        
        foreach( Trans::getTransactions( $pd , $trans ) as $entity )
        {
            $this->entityManager->persist( $entity );
        }
        
        $this->entityManager->flush();        
    }
    /**
     * Создание новой квитанции
     * @param type $contract Объект контракт
     * @param type $summa сумма квитанции
     */
    private function createInvoice($contract, $summa )
    {
        $invoice = new Invoice();
        $invoice->setN('new');
        $invoice->setSumma1($summa);
        $invoice->setSumma2(0);
        $invoice->setSumma3(0);
        $invoice->setStatus(1);
        $invoice->setContract($contract);
        $this->entityManager->persist($invoice);
        $this->entityManager->flush();
        
        $trans[] = array(
                    'iaccid'  => self::rest_detail,
                    'il1'     => $contract->getId(), 
                    'il2'     => $invoice->getId(), 
                    'il3'     => NULL,
                    'summa'   => $summa );
        $trans[] = array(
                    'iaccid'  => self::rest_total,
                    'il1'     => $contract->getId(), 
                    'il2'     => NULL, 
                    'il3'     => NULL,
                    'summa'   => $summa );
        
        $this->createTrans($invoice, $trans );
        
        return $invoice;
    }
    
}
