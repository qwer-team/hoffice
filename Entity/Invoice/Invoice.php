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
    

}