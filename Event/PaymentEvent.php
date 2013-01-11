<?php

namespace HOffice\AdminBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use HOffice\AdminBundle\Entity\Payment;

/**
 * Description of PaymentEvent
 *
 * @author root
 */
class PaymentEvent extends Event
{
    protected $payment;

    public function __construct($payment) {
        $this->payment = $payment;
    }
    
    public function getCreate()
    {
        return $this->payment;
    }    
}