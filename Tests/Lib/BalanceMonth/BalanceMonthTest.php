<?php

namespace HOffice\AdminBundle\Tests\Lib\BalanceMonth;
use Itc\AdminBundle\Tools\KernelAwareTest;
use HOffice\AdminBundle\Lib\Balance\BalanceService;
use HOffice\AdminBundle\Entity\Contract\Contract;
use HOffice\AdminBundle\Entity\Payment\Payment;
use HOffice\AdminBundle\Entity\Invoice\Invoice;
use Itc\DocumentsBundle\Entity\Pd\Trans;
use Itc\DocumentsBundle\Entity\Pd\Pdl;
use HOffice\AdminBundle\Entity\Service\Service;
use HOffice\AdminBundle\Entity\House\Apartment;

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
     * Количество контрактов (не менне 2-х)
     */
    const cnt_contract = 2;
    
    public function setUp() {
        parent::setUp();   
        $services = $this->createServices();        
        for($i = 0; $i < self::cnt_contract ; $i++)
        {
        $apartment = $this->createApartment();
        $contract = $this->createContract( $services );
        $contract->setApartment($apartment);
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
    /**
     * Все квитанции оплачены
     */
    public function testAllInvoiceClose()
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
                
        $invoices = $this->entityManager
                        ->getRepository("HOfficeAdminBundle:Invoice\Invoice")
                        ->findBy( array( "status" => self::pd_new ) );
        
        $this->assertEquals(self::cnt_contract , count($invoices));
        
        foreach($invoices as $invoice)
        {
            $lines = $invoice->getPdlines();
            $services = $invoice->getContract()->getServices();
            $this->assertEquals(count($services), count($lines));
        }
        
    }
    /**
     * Одна неоплаченая квитанция, перерасчет суммы квитанции
     */
    public function testOneInvoiceNotPaid()
    {
        $payments = $this->entityManager
                        ->getRepository("HOfficeAdminBundle:Payment\Payment")
                        ->findAll();        

        foreach ( $payments as $k=>$payment )
        {
            if( $k != 0 )
            {
                $payment->setStatus( self::pd_paid );
                $this->entityManager->persist ( $payment );
                $this->entityManager->flush();
            }
            else
            {
                $invoice_id = $payment->getInvoice()->getId();
                $invoice_sum = $payment->getInvoice()->getSumma1();
            }
        }      
        
        $this->service->execute();        

        $invoices = $this->entityManager
                        ->getRepository("HOfficeAdminBundle:Invoice\Invoice")
                        ->findAll();
        $cnt_new_invoices = 0;
        foreach($invoices as $invoice)
        {
            if($invoice->getStatus() == self::pd_new)
            {
                $lines = $invoice->getPdlines();
                $services = $invoice->getContract()->getServices();
                $this->assertEquals(count($services), count($lines));
                $cnt_new_invoices++;
            }
            else if ($invoice->getStatus() == self::pd_not_paid && 
                        $invoice->getId() == $invoice_id)
            {
                $this->assertTrue( $invoice_sum < $invoice->getSumma1());
            }
        }
        $this->assertEquals(self::cnt_contract, $cnt_new_invoices);        
    }    
    /**
     * Одна неоплаченая квитанция, оплата квитанции если у пользователя
     * есть дениги на счету
     */
    public function testOneInvoicePay()
    {
        $payments = $this->entityManager
                        ->getRepository("HOfficeAdminBundle:Payment\Payment")
                        ->findAll();        
        $this->assertEquals(self::cnt_contract, count($payments));
        
        foreach ( $payments as $k=>$payment )
        {
            if( $k != 0 )
            {
                $payment->setStatus( self::pd_paid );
                $this->entityManager->persist( $payment );
                $this->entityManager->flush();
            }
            else
            {
                $invoice_id = $payment->getInvoice()->getId();
                $invoice_sum = $payment->getInvoice()->getSumma1();
                $this->addMoneyToContractAccount($payment->getInvoice(),
                            $payment->getInvoice()->getContract(),
                            self::sum * 2 );
                $this->entityManager->remove( $payment );
                $this->entityManager->flush();                
            }
        }       
        
        $this->service->execute();        

        $invoices = $this->entityManager
                        ->getRepository("HOfficeAdminBundle:Invoice\Invoice")
                        ->findAll();
        $cnt_new_invoices = 0;
        foreach($invoices as $invoice)
        {
            if($invoice->getStatus() == self::pd_new)
            {
                $lines = $invoice->getPdlines();
                $services = $invoice->getContract()->getServices();
                $this->assertEquals(count($services), count($lines));
                $cnt_new_invoices++;
            }
            else if ($invoice->getId() == $invoice_id)
            {
                $this->assertTrue( $invoice->getStatus() == self::pd_paid);
                
                $this->checkDetailRest($invoice->getContract()->getId(), 
                                       $invoice->getId(), 
                                       0 );
            }
        }
        $this->assertEquals(self::cnt_contract, $cnt_new_invoices);                
    }
    /**
     * Одна неоплаченая квитанция, частичная оплата квитанции если у 
     * пользователя есть дениги на счету
     */
    public function testOneInvoicePartialPay()
    {
        $payments = $this->entityManager
                        ->getRepository("HOfficeAdminBundle:Payment\Payment")
                        ->findAll();        
        $this->assertEquals(self::cnt_contract, count($payments));
        
        foreach ( $payments as $k=>$payment )
        {
            if( $k != 0 )
            {
                $payment->setStatus( self::pd_paid );
                $this->entityManager->persist( $payment );
                $this->entityManager->flush();
            }
            else
            {
                $invoice_id = $payment->getInvoice()->getId();
                $invoice_sum = $payment->getInvoice()->getSumma1();
                $this->addMoneyToContractAccount($payment->getInvoice(),
                            $payment->getInvoice()->getContract(),
                            self::sum * 1.5 );
                $this->entityManager->remove( $payment );
                $this->entityManager->flush();                
            }
        }       
        
        $this->service->execute();        

        $invoices = $this->entityManager
                        ->getRepository("HOfficeAdminBundle:Invoice\Invoice")
                        ->findAll();
        $cnt_new_invoices = 0;
        foreach($invoices as $invoice)
        {
            if($invoice->getStatus() == self::pd_new)
            {
                $lines = $invoice->getPdlines();
                $services = $invoice->getContract()->getServices();
                $this->assertEquals(count($services), count($lines));
                $cnt_new_invoices++;
            }
            else if ($invoice->getId() == $invoice_id)
            {
                $this->assertTrue( $invoice->getStatus() == self::pd_not_paid);
                
                $this->checkDetailRest($invoice->getContract()->getId(), 
                                       $invoice->getId(), 
                                       self::sum * 1.5 - self::sum );
            }
        }
        $this->assertEquals(self::cnt_contract, $cnt_new_invoices);                
    }

    /**
     * Создание квартиры
     * @return \HOffice\AdminBundle\Entity\House\Apartment
     */
    private function createApartment()
    {
        $apartment = new Apartment();
        $apartment->setHouseId(11);
        $apartment->setKod(1);
        $apartment->setName(1);
        $apartment->setFloor(10);
        $apartment->setRooms(3);
        $apartment->setSBalcony(10);
        $apartment->setSWoBalcony(10);
        $apartment->setQWMeters(100);
        $apartment->setSAll(80);
        $apartment->setSLive(60);
        $this->entityManager->persist( $apartment );
        return $apartment;
    }

    /**
     * Создание сервисов
     * @return \HOffice\AdminBundle\Entity\Service\Service
     */
    private function createServices()
    {
        $services = array();
        for ($i = 0; $i < 5; $i++)
        {
            $services[$i] = new Service();
            $services[$i]->setKod($i);
            $services[$i]->setName("Service_".$i);
            $services[$i]->setPrice($i +1);
            $services[$i]->setPrice1(($i==0?1:$i) * 2);
            $this->entityManager->persist( $services[$i] );
        }
        $this->entityManager->flush();
        return $services;
    }

    /**
     * Создание контракта
     * @return \HOffice\AdminBundle\Entity\Contract\Contract
     */
    private function createContract( $services )
    {
        $contract = new Contract();
        $contract->setUserId(1);
        $contract->setApartmentId(5);
        $contract->setKod(1);
        $contract->setRegistered(5);
        foreach($services as $service)
            $contract->setServices($service);
        $this->entityManager->persist($contract);
        return $contract;
    }
    private function createPdlines( $invoice )
    {
        $services = $invoice->getContract()->getServices();
        $pdline_sum = round($invoice->getSumma1()/count($services), 2);
        foreach($services as $service)
        {
            $pdline = new Pdl();
            $pdline->setPd( $invoice );
            $pdline->setOa1( $service->getId() );
            $pdline->setSumma1( $pdline_sum );
            $pdline->setSumma2( 10 );
            $this->entityManager->persist( $pdline );            
        }        
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
        $invoice->setContract($contract);
        $this->entityManager->persist($invoice);
        $this->entityManager->flush();
        $this->createPdlines($invoice);
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
     * Добавление денег на счет
     * @param type $pd документ оплаты
     * @param type $sum сумма
     */
    private function addMoneyToContractAccount($pd, $contract, $sum )
    {   
        $trans[] = array(
            'oaccid'  => self::rest_total,
            'ol1'     => $contract->getId(), 
            'ol2'     => NULL, 
            'ol3'     => NULL,
            'summa'   => $sum );      
        
        $this->createTrans($pd, $trans );
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