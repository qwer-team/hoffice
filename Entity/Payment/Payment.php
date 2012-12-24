<?php

namespace HOffice\AdminBundle\Entity\Payment;

use Doctrine\ORM\Mapping as ORM;
use \Itc\DocumentsBundle\Entity\Pd\Pd;
use Symfony\Component\EventDispatcher\Event;
use \HOffice\AdminBundle\HOfficeEvents;
use \HOffice\AdminBundle\Event\PaymentEvent;


/**
 * Payment
 *
 * @ORM\Table()
 * @ORM\Entity
 * (
 *      repositoryClass="HOffice\AdminBundle\Entity\Payment\PaymentRepository"
 * )
 */
class Payment extends Pd {

    /**
     * @var integer
     *
     * @ORM\Column(name="contract_id", type="integer", nullable=true)
     */
    private $contract_id;
    /**
     * @ORM\ManyToOne
     * (
     *      targetEntity="HOffice\AdminBundle\Entity\Contract\Contract"
     * )
     * @ORM\JoinColumn
     * (
     *      name="contract_id", 
     *      referencedColumnName="id"
     * )
     */
    private $contract;
    /**
     * @var integer
     *
     * @ORM\Column(name="invoice_id", type="integer")
     */
    private $invoice_id;
    /**
     * @ORM\ManyToOne
     * (
     *      targetEntity="HOffice\AdminBundle\Entity\Invoice\Invoice",
     *      inversedBy="payments"
     * )
     * @ORM\JoinColumn
     * (
     *      name="invoice_id", 
     *      referencedColumnName="id"
     * )
     */
    private $invoice;   
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set contract
     *
     * @param integer $contract
     * @return PaymentPayment
     */
    public function setContract( $contract )
    {
        $this->contract = $contract;
    
        return $this;
    }

    /**
     * Get contractId
     *
     * @return integer 
     */
    public function getContract()
    {
        return $this->contract;
    }
    
    /**
     * Set contractId
     *
     * @param integer $contractId
     * @return PaymentPayment
     */
    public function setContractId( $contractId )
    {
        $this->contract_id = $contractId;
    
        return $this;
    }

    /**
     * Get contractId
     *
     * @return integer 
     */
    public function getContractId()
    {
        return $this->contract_id;
    }
    
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;
    
        return $this;
    }

    /**
     * Get menu
     *
     * @return string 
     */
    public function getInvoice()
    {
        return $this->invoice;
    }
    /** @ORM\PostUpdate() */
    public function createTransaction(){
        
        $container = \Itc\AdminBundle\ItcAdminBundle::getContainer();
        $dispatcher = $container->get("event_dispatcher");
        
        $event = new PaymentEvent($this);
        $dispatcher->dispatch("lala.fafa", $event);
        
    }

}
