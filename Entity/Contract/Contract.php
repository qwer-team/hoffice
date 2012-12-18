<?php

namespace HOffice\AdminBundle\Entity\Contract;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Itc\AdminBundle\Entity\TranslatableEntity;
use HOffice\AdminBundle\Entity\Contract\ContractProxy;
use HOffice\AdminBundle\Entity\Contract\ContractTranslation;

/**
 * HOffice\AdminBundle\Entity\Contract\Contract
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks 
 * @ORM\Table()
 */

class Contract extends TranslatableEntity 
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="kod", type="integer")
     */
    private $kod;

    /**
     * @var integer
     *
     * @ORM\Column(name="serial_number", type="integer")
     */
    private $serial_number;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var integer
     *
     * @ORM\Column(name="registered", type="integer")
     */
    private $registered;

    /**
     * @var integer
     * @Assert\NotNull()
     * @ORM\Column(name="user_id", type="integer")
     */
    private $user_id;

    /**
     * @ORM\ManyToOne(targetEntity="Itc\AdminBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;
    
    /**
     * @var integer
     * @Assert\NotNull()
     * @ORM\Column(name="apartment_id", type="integer")
     */
    private $apartment_id;

    /**
     * @ORM\ManyToOne(targetEntity="HOffice\AdminBundle\Entity\House\Apartment")
     * @ORM\JoinColumn(name="apartment_id", referencedColumnName="id", nullable=true )
     */
    private $apartment;
    /**
     * @var integer
     *
     * @ORM\Column(name="sale", type="integer")
     */
    private $sale;

    protected $fields = array('title');

    /**
    * @ORM\OneToMany(
    *     targetEntity="ContractTranslation",
    *     mappedBy="translatable",
    *     cascade={"persist"}
    * )
    */
    protected $translations;

    /**
     * @ORM\OneToMany(
     *     targetEntity="HOffice\AdminBundle\Entity\Invoice\Invoice",
     *     mappedBy="contract",
     *     cascade={"persist"}
     * )
     */
    protected $invoice;

    function __toString(){
        return is_null( $this->title ) ? "" : $this->title ;
    }

    public function __construct() {
        parent::__construct();
        $this->invoice = new \Doctrine\Common\Collections\ArrayCollection();        
    }

    public function getInvoice() {
        return $this->invoice;
    }

    public function setInvoice($invoice) {
        $this->invoice = $invoice;
    }    
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
     * Set kod
     *
     * @param integer $kod
     * @return Contract
     */
    public function setKod($kod)
    {
        $this->kod = $kod;
    
        return $this;
    }

    /**
     * Get kod
     *
     * @return integer 
     */
    public function getKod()
    {
        return $this->kod;
    }

    /**
     * Set serial_number
     *
     * @param integer $serialNumber
     * @return Contract
     */
    public function setSerialNumber($serialNumber)
    {
        $this->serial_number = $serialNumber;
    
        return $this;
    }

    /**
     * Get serial_number
     *
     * @return integer 
     */
    public function getSerialNumber()
    {
        return $this->serial_number;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Contract
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set registered
     *
     * @param integer $registered
     * @return Contract
     */
    public function setRegistered($registered)
    {
        $this->registered = $registered;
    
        return $this;
    }

    /**
     * Get registered
     *
     * @return integer 
     */
    public function getRegistered()
    {
        return $this->registered;
    }

    /**
     * Set user_id
     *
     * @param integer $userId
     * @return Contract
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;
    
        return $this;
    }

    /**
     * Get user_id
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set apartment_id
     *
     * @param integer $apartmentId
     * @return Contract
     */
    public function setApartmentId($apartmentId)
    {
        $this->apartment_id = $apartmentId;
    
        return $this;
    }

    /**
     * Get apartment_id
     *
     * @return integer 
     */
    public function getApartmentId()
    {
        return $this->apartment_id;
    }

    /**
     * Set sale
     *
     * @param integer $sale
     * @return Contract
     */
    public function setSale($sale)
    {
        $this->sale = $sale;
    
        return $this;
    }

    /**
     * Get sale
     *
     * @return integer 
     */
    public function getSale()
    {
        return $this->sale;
    }
    public function getUser()
    {
        return $this->user;
    }
    public function  setUser($user)
    {
        $this->user = $user;
        return $this;
    }
    public function getApartment()
    {
        return $this->apartment;
    }
    public function  setApartment($apartment)
    {
        $this->apartment = $apartment;
        return $this;
    }
    
        
}
