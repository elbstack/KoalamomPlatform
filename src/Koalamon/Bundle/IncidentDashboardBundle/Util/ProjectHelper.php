<?php

namespace Koalamon\Bundle\IncidentDashboardBundle\Util;

use Koalamon\Bundle\IncidentDashboardBundle\Entity\Event;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\EventIdentifier;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\Project;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\RawEvent;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\System;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\Tool;
use Koalamon\Bundle\IncidentDashboardBundle\EventListener\NewEventEvent;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProjectHelper
{
    private $entityManager;
    private $eventDispatcher;

    public function __construct(Container $container)
    {
        $this->entityManager = $container->get('doctrine')->getEntityManager();
        $this->eventDispatcher = $container->get('event_dispatcher');
    }

    public function addRawEvent(RawEvent $rawEvent, Project $project)
    {
        $event = Event::createFromRawEvent($rawEvent);

        $identifier = $this->entityManager
            ->getRepository('KoalamonIncidentDashboardBundle:EventIdentifier')
            ->findOneBy(array('project' => $project, 'identifier' => $rawEvent->getIdentifier()));

        if (is_null($identifier)) {
            $identifier = new EventIdentifier();
            $identifier->setProject($project);
            $identifier->setIdentifier($rawEvent->getIdentifier());
            $this->entityManager->persist($identifier);
            $this->entityManager->flush();
        }

        $event->setEventIdentifier($identifier);

        $event = $this->translate($event);

        $system = $this->entityManager
            ->getRepository('KoalamonIncidentDashboardBundle:System')
            ->findOneBy(['project' => $project, 'identifier' => $event->getSystem()]);

        if (is_null($system)) {
            $system = new System();
            $system->setIdentifier($rawEvent->getSystem());
            $system->setName($rawEvent->getSystem());
            $system->setProject($project);

            $this->entityManager->persist($system);
            $this->entityManager->flush();
        }

        $identifier->setSystem($system);

        $this->addEvent($event);
    }

    public function addEvent(Event $event)
    {
        $this->handleTool($event);

        $project = $event->getEventIdentifier()->getProject();

        $lastEvent = $this->entityManager
            ->getRepository('KoalamonIncidentDashboardBundle:Event')
            ->findOneBy(array("eventIdentifier" => $event->getEventIdentifier()), array("created" => "DESC"));
       
        // is status change?
        if (is_null($lastEvent) || $lastEvent->getStatus() != $event->getStatus()) {
            $event->setIsStatusChange(true);

            if ($event->getStatus() == Event::STATUS_SUCCESS) {
                if (!is_null($lastEvent) && !$event->getEventIdentifier()->isKnownIssue()) {
                    $project->decOpenIncidentCount();
                }

                if (is_null($event->getEventIdentifier()->getLastEvent())) {
                    $occurrenceLastEvent = $event->getCreated();
                } else {
                    $occurrenceLastEvent = $event->getEventIdentifier()->getLastEvent()->getLastStatusChange();
                }
                $occurrenceCurrentEvent = $event->getCreated();

                $timeToRecover = abs(($occurrenceCurrentEvent->getTimestamp() - $occurrenceLastEvent->getTimestamp()) / 60);
                $event->getEventIdentifier()->addNewFailure($timeToRecover);
            } else {
                $project->incOpenIncidentCount();
            }

            $project->setLastStatusChange($event->getCreated());
            $event->setLastStatusChange($event->getCreated());
        } else {
            $event->setLastStatusChange($lastEvent->getLastStatusChange());
        }

        $event->getEventIdentifier()->setLastEvent($event);
        $event->getEventIdentifier()->setCurrentState($event->getStatus());

        $event->getEventIdentifier()->incEventCount();
        if ($event->getStatus() == Event::STATUS_FAILURE) {
            $event->getEventIdentifier()->incFailedEventCount();
        }

        $this->storeData($event, $project);

        $dispatcherEvent = new NewEventEvent($event);
        if ($lastEvent) {
            $dispatcherEvent->setLastEvent($lastEvent);
        }
        $this->eventDispatcher->dispatch('koalamon.event.create', $dispatcherEvent);
    }

    private function handleTool(Event &$event)
    {
        $toolName = $event->getType();

        $tool = $this->entityManager->getRepository('KoalamonIncidentDashboardBundle:Tool')
            ->findOneBy(array('project' => $event->getEventIdentifier()->getProject(), 'identifier' => $toolName));

        if (is_null($tool)) {
            $tool = new Tool();
            $tool->setProject($event->getEventIdentifier()->getProject());
            $tool->setIdentifier($toolName);
            $tool->setActive(false);

            $this->entityManager->persist($tool);
            $this->entityManager->flush();
        }

        $event->getEventIdentifier()->setTool($tool);
    }

    private function storeData(Event $event, Project $project)
    {
        $this->entityManager->persist($event);
        $this->entityManager->persist($project);
        $this->entityManager->flush();

        $this->entityManager->persist($event->getEventIdentifier());
        $this->entityManager->flush();
    }

    private function translate(Event $event)
    {
        $translations = $this->entityManager
            ->getRepository('KoalamonIncidentDashboardBundle:Translation')
            ->findBy(array('project' => $event->getEventIdentifier()->getProject()));

        foreach ($translations as $translation) {
            if (preg_match('^' . $translation->getIdentifier() . '^', $event->getEventIdentifier()->getIdentifier())) {
                return $translation->translate($event);
            }
        }

        return $event;
    }
}
