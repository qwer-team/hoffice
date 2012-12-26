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
   
    public function getContract() {
        return $this->contract;
    }

    public function setContract(Contract $contract) {
        $this->contract = $contract;
        //$this->contract_id = $contract->getId();
    }
    public function getContractId() {
        //$this->contract_id = $this->getContract()->getId();
        return $this->contract_id;
    }

    public function setContractId($contract_id) {
        $this->contract_id = $contract_id;
    }
    /** @ORM\PostLoad */
    public function resetContactId()
    {
        $this->contract_id = $this->getContractId();
    }
}
