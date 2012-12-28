<?php

namespace HOffice\AdminBundle\Tests\Lib\Payment;
use Itc\AdminBundle\Tools\KernelAwareTest;
use HOffice\AdminBundle\Entity\Payment\Payment;
use HOffice\AdminBundle\Entity\Invoice\Invoice;
use HOffice\AdminBundle\Entity\Contract\Contract;
use HOffice\AdminBundle\Lib\Payment\Service;
use Itc\DocumentsBundle\Entity\Pd\Trans;

class ServiceTest extends KernelAwareTest 
{
    private $service;
    private $payment;
    private static $startmonth;
    private static $endmonth;    

    public function setUp() {
        parent::setUp();
        $contract = new Contract();
        $contract->setUserId(1);
        $contract->setApartmentId(5);
        $contract->setKod(1);
        $contract->setRegistered(5);
        $this->entityManager->persist($contract);
//        $this->entityManager->flush($contract);        
        
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
                    'iaccid'  => 1,
                    'il1'     => $contract->getId(), 
                    'il2'     => $invoice->getId(), 
                    'il3'     => NULL,
                    'summa'   => 100 );
        $trans[] = array(
                    'iaccid'  => 3,
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
        
        $this->service = new Service($this->payment, $this->container);

        $param = \Itc\AdminBundle\ItcAdminBundle::getContainer();
        if ( !isset( self::$startmonth ) )
            self::$startmonth = $param->getParameter( "startMonth" );
        if ( !isset( self::$endmonth ) )
            self::$endmonth = $param->getParameter( "endMonth" );
  
    }
    /*
     * Cумма оплаты равна сумме квитанции
     */
    public function testSummEqual()
    {
        $this->paymentSummas(100, 10, 1);
        
        
        $repo = $this->entityManager->getRepository("ItcDocumentsBundle:Pd\Rest");
        
        $invoice_rest = $repo->findBy( array( 
                'l1' => $this->payment->getInvoice()->getContract()->getId()
                 ) );
        
        $this->assertEquals(self::$endmonth * 2, count($invoice_rest));
        
        $this->service->execute();
        
        $this->assertEquals( $this->payment->getInvoice()->getStatus(), 2);
        
        $ent = $repo->findAll();
        \Doctrine\Common\Util\Debug::dump(count($ent)."==".self::$endmonth * 2);
        foreach($ent as $n){
        }
        $this->assertEquals( self::$endmonth * 2, count($trans) );       
    }
 /*   
    public function testSummLess()
    {
        $this->paymentSummas(50, 5, 1);
        $this->service->execute();
        
        $repo = $this->entityManager->getRepository("ItcDocumentsBundle:Pd\Rest");              
        
        $ent = $repo->findAll();
        foreach($ent as $n){
//            \Doctrine\Common\Util\Debug::dump($n->getM()."-".$n->getY()."sd=".$n->getSd()."oc=".$n->getOc()."od=".$n->getOd());
        }
        $this->assertEquals(48, count($ent));
    }

    public function testSummMore()
    {
        $this->paymentSummas(200, 10, 1);
        $this->service->execute();
        
        $repo = $this->entityManager->getRepository("ItcDocumentsBundle:Pd\Rest");
              
        
        $ent = $repo->findAll();
        foreach($ent as $n){
            //\Doctrine\Common\Util\Debug::dump($n->getM()."-".$n->getY()."sd=".$n->getSd()."oc=".$n->getOc()."od=".$n->getOd());
        }
/*  
        $repo = $this->entityManager->getRepository("ItcDocumentsBundle:Pd\Trans");
        
        $trans = $repo->findAll();
        $this->assertEquals(1, count($trans));
         
        $this->assertEquals(72, count($ent));
    }*/
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
        $em = $this->container->get("doctrine")->getEntityManager();        
        
        foreach( Trans::getTransactions( $pd , $trans ) as $entity )
        {
            $em->persist( $entity );
        }
        
        $em->flush();        
    }
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
//        $this->entityManager->flush($invoice);
        
        $trans[] = array(
                    'iaccid'  => 1,
                    'il1'     => $contract->getId(), 
                    'il2'     => $invoice->getId(), 
                    'il3'     => NULL,
                    'summa'   => $summa );
        $trans[] = array(
                    'iaccid'  => 3,
                    'il1'     => $contract->getId(), 
                    'il2'     => NULL, 
                    'il3'     => NULL,
                    'summa'   => $summa );
        
        $this->createTrans($invoice, $trans );
    }
    
}
