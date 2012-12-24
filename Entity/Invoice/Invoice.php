<?php

namespace HOffice\AdminBundle\Entity\Invoice;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Itc\DocumentsBundle\Entity\Pd\Pd;
use Symfony\Component\Validator\Constraints as Assert;
use HOffice\AdminBundle\Entity\Contract\Contract;

/**
 * Invoice
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Invoice extends Pd{
    
    /**
     * @var integer
     * @Assert\NotNull()
     * @ORM\Column(name="contract_id", type="integer", nullable=true)
     */
    private $contract_id;

    /**
     * @Assert\NotNull()
     * @ORM\JoinColumn(name="contract_id", referencedColumnName="id", nullable=true)
     * @ORM\ManyToOne(targetEntity="HOffice\AdminBundle\Entity\Contract\Contract", inversedBy="invoice")
     */
    private $contract;
    /**
     * @ORM\OneToMany(
     *     targetEntity="HOffice\AdminBundle\Entity\Payment\Payment",
     *     mappedBy="invoice",
     *     cascade={"persist"}
     * )
     */
    private $payments;
   
    public function __construct() {
        $this->payments = new \Doctrine\Common\Collections\ArrayCollection();
        parent::__construct();
    }
    
    public function getContract() {
        return $this->contract;
    }

    public function setContract(Contract $contract) {
        $this->contract = $contract;
        $this->contract_id = $contract->getId();
    }
    public function getContractId() {
        return $this->contract_id;
    }

    public function setContractId($contract_id) {
        $this->contract_id = $contract_id;
    }
    public function  setPayments($payment)
    {
        $this->payments[] = $payment;
    }
    public function  getPayments()
    {
        return $this->payments;
    }   

}