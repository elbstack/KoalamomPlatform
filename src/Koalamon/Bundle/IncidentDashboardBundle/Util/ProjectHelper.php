<?php

namespace Koalamon\Bundle\IncidentDashboardBundle\Util;

use Koalamon\Bundle\IncidentDashboardBundle\Entity\Event;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\EventIdentifier;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\Project;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\RawEvent;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\System;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\Tool;
use Koalamon\Bundle\IncidentDashboardBundle\EventListener\NewEventEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class ProjectHelper
 *
 * @todo should be handled via dependency injection
 *
 * @package Koalamon\Bundle\IncidentDashboardBundle\Util
 */
class ProjectHelper
{
    static public function addRawEvent(Router $router, EntityManager $doctrineManager, RawEvent $rawEvent, Project $project, EventDispatcherInterface $eventDispatcher)
    {
        $event = new Event();

        $event->setStatus($rawEvent->getStatus());
        $event->setMessage($rawEvent->getMessage());
        $event->setSystem($rawEvent->getSystem());
        $event->setType($rawEvent->getType());
        $event->setUnique($rawEvent->isUnique());
        $event->setUrl($rawEvent->getUrl());
        $event->setValue($rawEvent->getValue());

        $identifier = $doctrineManager->getRepository('KoalamonIncidentDashboardBundle:EventIdentifier')
            ->findOneBy(array('project' => $project, 'identifier' => $rawEvent->getIdentifier()));

        if (is_null($identifier)) {
            $identifier = new EventIdentifier();
            $identifier->setProject($project);
            $identifier->setIdentifier($rawEvent->getIdentifier());
            $doctrineManager->persist($identifier);
            $doctrineManager->flush();
        }

        $event->setEventIdentifier($identifier);

        $event = self::translate($event, $doctrineManager);

        $system = $doctrineManager->getRepository('KoalamonIncidentDashboardBundle:System')->findOneBy(['project' => $project, 'identifier' => $event->getSystem()]);

        if(is_null($system)) {
            $system = new System();
            $system->setIdentifier($rawEvent->getSystem());
            $system->setName($rawEvent->getSystem());
            $system->setProject($project);

            $doctrineManager->persist($system);
            $doctrineManager->flush();
        }

        $identifier->setSystem($system);

        self::addEvent($router, $doctrineManager, $event, $eventDispatcher);
    }

    static public function addEvent(Router $router, EntityManager $doctrineManager, Event $event, EventDispatcherInterface $eventDispatcher)
    {
        self::handleTool($event, $doctrineManager);

        $project = $event->getEventIdentifier()->getProject();

        $lastEvent = $doctrineManager
            ->getRepository('KoalamonIncidentDashboardBundle:Event')
            ->findOneBy(array("eventIdentifier" => $event->getEventIdentifier()), array("created" => "DESC"));
        /** @var Event $lastEvent */

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

        self::storeData($doctrineManager, $event, $project);

        $dispatcherEvent = new NewEventEvent($event);
        if ($lastEvent) {
            $dispatcherEvent->setLastEvent($lastEvent);
        }
        $eventDispatcher->dispatch('koalamon.event.create', $dispatcherEvent);
    }

    static private function handleTool(Event &$event, EntityManager $doctrineManager)
    {
        $toolName = $event->getType();

        $tool = $doctrineManager->getRepository('KoalamonIncidentDashboardBundle:Tool')
            ->findOneBy(array('project' => $event->getEventIdentifier()->getProject(), 'identifier' => $toolName));

        if (is_null($tool)) {
            $tool = new Tool();
            $tool->setProject($event->getEventIdentifier()->getProject());
            $tool->setIdentifier($toolName);
            $tool->setActive(false);

            $doctrineManager->persist($tool);
            $doctrineManager->flush();
        }

        $event->getEventIdentifier()->setTool($tool);
    }

    static private function storeData(EntityManager $doctrineManager, Event $event, Project $project)
    {
        $doctrineManager->persist($event);
        $doctrineManager->persist($project);
        $doctrineManager->flush();

        $doctrineManager->persist($event->getEventIdentifier());
        $doctrineManager->flush();
    }

    static private function translate(Event $event, EntityManager $doctrineManager)
    {
        $translations = $doctrineManager
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
