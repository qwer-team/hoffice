<?php

namespace HOffice\AdminBundle\Entity\Payment;

use Doctrine\ORM\Mapping as ORM;
use \Itc\DocumentsBundle\Entity\Pd\Pd;

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
     * @ORM\JoinColumn
     * (
     *      name="contractId", 
     *      referencedColumnName="id"
     * )
     * @ORM\ManyToOne
     * (
     *      targetEntity="HOffice\AdminBundle\Entity\Contract\Contract", 
     *      inversedBy="id"
     * )
     */
    private $contract;

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
     * Get contract
     *
     * @return integer 
     */
    public function getContract()
    {
        return $this->contract;
    }

}
