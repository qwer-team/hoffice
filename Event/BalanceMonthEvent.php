<?php

namespace HOffice\AdminBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Description of PaymentEvent
 *
 * @author root
 */
class BalanceMonthEvent extends Event
{
    protected $event;

    public function __construct($event) {
        $this->event = $event;
    }
    
    public function getEvent()
    {
        return $this->event;
    }    
}