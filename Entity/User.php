<?php

namespace HOffice\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Itc\AdminBundle\Entity\User as IUser;
/**
 * Itc\AdminBundle\Entity\User
 *
 * @ORM\Table()
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class User extends IUser
{
    /**
     * @ORM\OneToMany (
     *     targetEntity="Itc\DocumentsBundle\Entity\Pd\Pd",
     *     mappedBy="name_ucor",
     *     cascade={"persist"}
     * )
     */
    private $documents;
    
        public function __construct()
        {
            
            $this->documents = new \Doctrine\Common\Collections\ArrayCollection();
        }
      
}
