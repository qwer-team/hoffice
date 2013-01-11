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
 */
class tag {
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
     * @ORM\Column(name="name", type="integer")
     */
    public $name;
    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}

?>
