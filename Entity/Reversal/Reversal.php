<?php

namespace HOffice\AdminBundle\Entity\Reversal;

use Doctrine\ORM\Mapping as ORM;
use \Itc\DocumentsBundle\Entity\Pd\Pd;
use \Doctrine\Common\Collections\ArrayCollection;

/**
 * Reversal
 *
 * @ORM\Table()
 * @ORM\Entity
 * (
 *      repositoryClass="HOffice\AdminBundle\Entity\Reversal\ReversalRepository"
 * )
 */
class Reversal extends Pd {

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

    private $tags;

    //private $pdlines;
    
    public function __construct()
    {
        $this->tags = new ArrayCollection();
        parent::__construct();
    }
    
    public function gettags()
    {
        return $this->tags;
    }
    
    public function settags( ArrayCollection $tags )
    {
        foreach( $tags as $pdline ){

            $pdline->addReversal( $this );

        }
        
        return $this;
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

}
