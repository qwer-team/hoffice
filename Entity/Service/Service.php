<?php

namespace HOffice\AdminBundle\Entity\Service;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Itc\AdminBundle\Entity\Unit\Unit;

/**
 * Service
 *
 * @ORM\Table()
 * @ORM\Entity
 * @Gedmo\Loggable
 */
class Service
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
     * @var string
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var float
     * @Gedmo\Versioned
     * @ORM\Column(name="price", type="decimal")
     */
    private $price;

    /**
     * @var float
     * @Gedmo\Versioned
     * @ORM\Column(name="price1", type="decimal")
     */
    private $price1;

    /**
     * @ORM\ManyToOne(targetEntity="Itc\AdminBundle\Entity\Unit\Unit")
     * @ORM\JoinColumn(name="unit_id", referencedColumnName="id",
     * nullable=true)
     */
    private $unit;

   /**
     * @ORM\ManyToMany(targetEntity="HOffice\AdminBundle\Entity\Contract\Contract", mappedBy="services")
     */
    private $contracts;
    /**
     * @var string
     * @ORM\Column(name="tag", type="string", length=255)
     */
    private $tag;
    
    public function __construct() {
        //parent::__construct();
        $this->contracts = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add contracts
     *
     * @param Itc\AdminBundle\Entity\Contract\Contract $contracts
     * @return Keyword
     */
    public function addContracts(\Itc\AdminBundle\Entity\Contract\Contract $contracts)
    {
        $this->contracts[] = $contracts;
    
        return $this;
    }

    /**
     * Remove contracts
     *
     * @param Itc\AdminBundle\Entity\Contract\Contract $contracts
     */
    public function removeContracts(\Itc\AdminBundle\Entity\Contract\Contract $contracts)
    {
        $this->contracts->removeElement($contracts);
    }

    /**
     * Get contracts
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getContracts()
    {
        return $this->contracts;
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
     * @return Service
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
     * Set name
     *
     * @param string $name
     * @return Service
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }
        /**
     * Set name
     *
     * @param string $name
     * @return Service
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getTag()
    {
        return $this->tag;
    }
    /**
     * Set price
     *
     * @param float $price
     * @return Service
     */
    public function setPrice($price)
    {
        $this->price = $price;
    
        return $this;
    }

    /**
     * Get price
     *
     * @return float 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set price1
     *
     * @param float $price1
     * @return Service
     */
    public function setPrice1($price1)
    {
        $this->price1 = $price1;
    
        return $this;
    }

    /**
     * Get price1
     *
     * @return float 
     */
    public function getPrice1()
    {
        return $this->price1;
    }

    /**
     * Set unit
     *
     * @param \Itc\AdminBundle\Entity\Unit\Unit $unit
     * @return Service
     */
    public function setUnit(\Itc\AdminBundle\Entity\Unit\Unit $unit = null)
    {
        $this->unit = $unit;
    
        return $this;
    }

    /**
     * Get unit
     *
     * @return \Itc\AdminBundle\Entity\Unit\Unit 
     */
    public function getUnit()
    {
        return $this->unit;
    }
}