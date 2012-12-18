<?php

namespace HOffice\AdminBundle\Entity\Invoice;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Itc\DocumentsBundle\Entity\Pd\Pd;

/**
 * Invoice
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Invoice extends Pd{
    
    /**
     * @ORM\JoinColumn(name="contract_id", referencedColumnName="id")
     * @ORM\ManyToOne(targetEntity="HOffice\AdminBundle\Entity\Contract\Contract", inversedBy="invoice")
     */
    private $contract;
    
    public function getContract() {
        return $this->contract;
    }

    public function setContract($contract) {
        $this->contract = $contract;
    }

}