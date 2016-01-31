<?php

namespace Koalamon\Bundle\IncidentDashboardBundle\EventListener;

use Symfony\Component\EventDispatcher\Event;

class NewEventEvent extends Event
{
    private $event;
    private $lastEvent;

    /**
     * NewEventEvent constructor.
     */
    public function __construct(\Koalamon\Bundle\IncidentDashboardBundle\Entity\Event $event)
    {
        $this->event = $event;
    }

    public function setLastEvent(\Koalamon\Bundle\IncidentDashboardBundle\Entity\Event $event)
    {
        $this->lastEvent = $event;
    }

    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return \Koalamon\Bundle\IncidentDashboardBundle\Entity\Event
     */
    public function getLastEvent()
    {
        return $this->lastEvent;
    }

    public function hasLastEvent()
    {
        return !is_null($this->lastEvent);
    }
}