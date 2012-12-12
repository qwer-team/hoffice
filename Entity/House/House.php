<?php

namespace HOffice\AdminBundle\Entity\House;

use Doctrine\ORM\Mapping as ORM;

/**
 * House
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="HOffice\AdminBundle\Entity\House\HouseRepository")
 */
class House
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $street
     *
     * @ORM\Column(name="street", type="string", length=255)
     */
    private $street;

    /**
     * @var string $number
     *
     * @ORM\Column(name="number", type="string", length=10)
     */
    private $number;
    
    /**
     * @ORM\OneToMany(
     *     targetEntity="HOffice\AdminBundle\Entity\House\Apartment",
     *     mappedBy="house",
     *     cascade={"persist"}
     * )
     */
    protected $apartments;

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
     * Set street
     *
     * @param string $street
     * @return House
     */
    public function setStreet($street)
    {
        $this->street = $street;
    
        return $this;
    }

    /**
     * Get street
     *
     * @return string 
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set number
     *
     * @param string $number
     * @return House
     */
    public function setNumber($number)
    {
        $this->number = $number;
    
        return $this;
    }

    /**
     * Get number
     *
     * @return string 
     */
    public function getNumber()
    {
        return $this->number;
    }
    
    public function getApartments() {
        return $this->apartments;
    }

    public function setApartments($apartments) {
        $this->apartments = $apartments;
    }

    public function __toString() {
        return (string)$this->id;
    }
}
