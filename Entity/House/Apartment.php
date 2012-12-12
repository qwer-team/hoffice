<?php

namespace HOffice\AdminBundle\Entity\House;

use Doctrine\ORM\Mapping as ORM;

/**
 * Apartment
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Apartment
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
     * @var integer $houseId
     *
     * @ORM\Column(name="houseId", type="integer")
     */
    private $houseId;

    /**
     * @var integer $kod
     *
     * @ORM\Column(name="kod", type="smallint")
     */
    private $kod;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=10)
     */
    private $name;

    /**
     * @var integer $floor
     *
     * @ORM\Column(name="floor", type="smallint")
     */
    private $floor;

    /**
     * @var integer $rooms
     *
     * @ORM\Column(name="rooms", type="smallint")
     */
    private $rooms;

    /**
     * @var integer $q_w_meters
     *
     * @ORM\Column(name="q_w_meters", type="smallint")
     */
    private $q_w_meters;

    /**
     * @var float $s_live
     *
     * @ORM\Column(name="s_live", type="decimal")
     */
    private $s_live;

    /**
     * @var float $s_all
     *
     * @ORM\Column(name="s_all", type="decimal")
     */
    private $s_all;

    /**
     * @var float $s_balcony
     *
     * @ORM\Column(name="s_balcony", type="decimal")
     */
    private $s_balcony;

    /**
     * @var float $s_wo_balcony
     *
     * @ORM\Column(name="s_wo_balcony", type="decimal")
     */
    private $s_wo_balcony;

    /**
     * @ORM\JoinColumn(name="houseId", referencedColumnName="id",
     * onDelete="CASCADE")
     * @ORM\ManyToOne(targetEntity="HOffice\AdminBundle\Entity\House\House", inversedBy="apartments")
     */
    private $house;

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
     * Set houseId
     *
     * @param integer $houseId
     * @return Apartment
     */
    public function setHouseId($houseId)
    {
        $this->houseId = $houseId;
    
        return $this;
    }

    /**
     * Get houseId
     *
     * @return integer 
     */
    public function getHouseId()
    {
        return $this->houseId;
    }

    /**
     * Set kod
     *
     * @param integer $kod
     * @return Apartment
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
     * @return Apartment
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
     * Set floor
     *
     * @param integer $floor
     * @return Apartment
     */
    public function setFloor($floor)
    {
        $this->floor = $floor;
    
        return $this;
    }

    /**
     * Get floor
     *
     * @return integer 
     */
    public function getFloor()
    {
        return $this->floor;
    }

    /**
     * Set rooms
     *
     * @param integer $rooms
     * @return Apartment
     */
    public function setRooms($rooms)
    {
        $this->rooms = $rooms;
    
        return $this;
    }

    /**
     * Get rooms
     *
     * @return integer 
     */
    public function getRooms()
    {
        return $this->rooms;
    }

    /**
     * Set q_w_meters
     *
     * @param integer $qWMeters
     * @return Apartment
     */
    public function setQWMeters($qWMeters)
    {
        $this->q_w_meters = $qWMeters;
    
        return $this;
    }

    /**
     * Get q_w_meters
     *
     * @return integer 
     */
    public function getQWMeters()
    {
        return $this->q_w_meters;
    }

    /**
     * Set s_live
     *
     * @param float $sLive
     * @return Apartment
     */
    public function setSLive($sLive)
    {
        $this->s_live = $sLive;
    
        return $this;
    }

    /**
     * Get s_live
     *
     * @return float 
     */
    public function getSLive()
    {
        return $this->s_live;
    }

    /**
     * Set s_all
     *
     * @param float $sAll
     * @return Apartment
     */
    public function setSAll($sAll)
    {
        $this->s_all = $sAll;
    
        return $this;
    }

    /**
     * Get s_all
     *
     * @return float 
     */
    public function getSAll()
    {
        return $this->s_all;
    }

    /**
     * Set s_balcony
     *
     * @param float $sBalcony
     * @return Apartment
     */
    public function setSBalcony($sBalcony)
    {
        $this->s_balcony = $sBalcony;
    
        return $this;
    }

    /**
     * Get s_balcony
     *
     * @return float 
     */
    public function getSBalcony()
    {
        return $this->s_balcony;
    }

    /**
     * Set s_wo_balcony
     *
     * @param float $sWoBalcony
     * @return Apartment
     */
    public function setSWoBalcony($sWoBalcony)
    {
        $this->s_wo_balcony = $sWoBalcony;
    
        return $this;
    }

    /**
     * Get s_wo_balcony
     *
     * @return float 
     */
    public function getSWoBalcony()
    {
        return $this->s_wo_balcony;
    }
    
    public function getHouse() {
        return $this->house;
    }

    public function setHouse($house) {
        $this->house = $house;
    }
    
    public function __toString() {
        return (string)$this->id;
    }
    
}
