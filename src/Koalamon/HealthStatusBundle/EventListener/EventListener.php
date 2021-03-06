<?php

namespace Koalamon\HealthStatusBundle\EventListener;

use Koalamon\Bundle\IncidentDashboardBundle\Entity\Event;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\RawEvent;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\System;
use Koalamon\Bundle\IncidentDashboardBundle\EventListener\NewEventEvent;
use Koalamon\Bundle\IncidentDashboardBundle\Util\ProjectHelper;
use Koalamon\NotificationBundle\Sender\SenderFactory;
use Koalamon\NotificationBundle\Sender\VariableContainer;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class EventListener
{
    private $doctrineManager;
    private $router;
    private $container;
    private $dispatcher;

    const TOOL_IDENTIFIER = 'system_health_status';

    public function __construct(ContainerInterface $container)
    {
        $this->doctrineManager = $container->get('doctrine')->getManager();
        $this->router = $container->get('Router');
        $this->dispatcher = $container->get('event_dispatcher');
        $this->container = $container;
    }

    public function onEventCreate(NewEventEvent $event)
    {
        $currentEvent = $event->getEvent();
        $lastEvent = $event->getLastEvent();

        if ($currentEvent->getType() == self::TOOL_IDENTIFIER) {
            return;
        }

        if (!is_null($lastEvent) && $currentEvent->getStatus() == $lastEvent->getStatus()) {
            return;
        }

        $system = $currentEvent->getEventIdentifier()->getSystem();
        if (is_null($system)) {
            return;
        }

        $tool = $currentEvent->getEventIdentifier()->getTool();

        $status = Event::STATUS_SUCCESS;
        $message = '';

        if ($system->getThreshold() == 0 || $currentEvent->getStatus() == Event::STATUS_SUCCESS) {
            $healthStatus = max(0, $system->getHealthStatus() - $tool->getScore());
        } else {
            $healthStatus = $system->getHealthStatus() + $tool->getScore();
            if ($healthStatus >= $system->getThreshold()) {
                $status = Event::STATUS_FAILURE;
                $message = 'The health status of "' . $system->getName() . '" is critical.';
            }
        }

        $system->setHealthStatus($healthStatus);

        $this->doctrineManager->persist($system);
        $this->doctrineManager->flush();

        $rawEvent = new RawEvent();
        $rawEvent->setSystem($system->getIdentifier());
        $rawEvent->setType(self::TOOL_IDENTIFIER);
        $rawEvent->setUnique(false);
        $rawEvent->setValue($healthStatus);
        $rawEvent->setStatus($status);
        $rawEvent->setMessage($message);
        $rawEvent->setIdentifier(self::TOOL_IDENTIFIER . '_' . $system->getIdentifier());

        $this->container->get('koalamon.project.helper')->addRawEvent($rawEvent, $currentEvent->getEventIdentifier()->getProject());
    }
}
