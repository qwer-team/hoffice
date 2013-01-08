<?php

namespace HOffice\AdminBundle\Tests\Lib\BalanceMonth;
use Itc\AdminBundle\Tools\KernelAwareTest;
use HOffice\AdminBundle\Entity\Contract\Contract;

/**
 * Description of BalanceMonthTest
 *
 * @author root
 */
class BalanceMonthTest extends KernelAwareTest
{
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
        return parent::setUp();
        if ( !isset($this->y) || !isset($this->m))
            list($this->y, $this->m) = explode(",", 
               date("Y,m", mktime(0, 0, 0, date("n") + 1)));
        
    }
    
    public function testBalance()
    {
        echo "agga";
         $this->assertEquals(1, 1);
         
         $contract = $this->createContract();
         
         $this->checkTotalRest( $contract->getId(), 0 );
         
    }
    private function createContract()
    {
        $contract = new Contract();
        $contract->setUserId(1);
        $contract->setApartmentId(5);
        $contract->setKod(1);
        $contract->setRegistered(5);
        $this->entityManager->persist($contract);
        $this->entityManager->flush();
        return $contract;
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
    
}