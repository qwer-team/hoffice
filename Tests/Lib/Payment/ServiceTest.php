<?php

namespace HOffice\AdminBundle\Tests\Lib\Payment;
use Itc\AdminBundle\Tools\KernelAwareTest;
use HOffice\AdminBundle\Entity\Payment\Payment;
use HOffice\AdminBundle\Entity\Invoice\Invoice;
use HOffice\AdminBundle\Lib\Payment\Service;

class ServiceTest extends KernelAwareTest 
{
    private $service;
    private $payment;
    
    public function setUp() {
        parent::setUp();
        $invoice = new Invoice();
        $invoice->setN(1);
        $invoice->setContractId(1);
        $invoice->setSumma1(100);
        $invoice->setSumma2(1);
        $invoice->setSumma3(1);
        $this->entityManager->persist($invoice);
        $this->entityManager->flush($invoice);
        
        $this->payment = new Payment();
        $this->payment->setN(1);
        $this->payment->setStatus(1);
        $this->payment->setInvoice($invoice);
        
        $this->service = new Service($this->payment, $this->container);
    }
    
    public function testSummEqual()
    {
        $this->paymentSummas(100, 10, 1);
           
        $repo = $this->entityManager->getRepository("ItcDocumentsBundle:Pd\Rest");
        $ent = $repo->findAll();
        $this->assertEquals(0, count($ent));
        
        $this->service->execute();
                      
        $ent = $repo->findAll();
        foreach($ent as $n){
//            \Doctrine\Common\Util\Debug::dump(//"l1=".$n->getL1()."l2=".$n->getL2()."l3=".$n->getL3());
//            \Doctrine\Common\Util\Debug::dump($n->getM()."-".$n->getY()."sd=".$n->getSd()."oc=".$n->getOc()."od=".$n->getOd()."Iacc=".$n->getAccId());
        }
//        \Doctrine\Common\Util\Debug::dump($this->payment);
//        \Doctrine\Common\Util\Debug::dump($invoice);
        $repo = $this->entityManager->getRepository("ItcDocumentsBundle:Pd\Trans");
        $trans = $repo->findAll();
        foreach($trans as $n){
            \Doctrine\Common\Util\Debug::dump("sum=".$n->getSumma()."iacc=".$n->getIaccId()."oacc=".$n->getOaccId()."pd=".$n->getPd()->getId());
        }
  //      $this->assertEquals(1, count($trans));
        
        $this->assertEquals(48, count($ent));
    }
    
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
         
*/        $this->assertEquals(72, count($ent));
    }
    private function paymentSummas($summa1, $summa2, $summa3)
    {
        $this->payment->setSumma1($summa1);
        $this->payment->setSumma2($summa2);
        $this->payment->setSumma3($summa3);
        $this->entityManager->persist($this->payment);
        $this->entityManager->flush($this->payment);
    }
}
