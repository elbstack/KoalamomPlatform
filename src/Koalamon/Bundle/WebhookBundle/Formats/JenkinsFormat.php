<?php

namespace Koalamon\Bundle\WebhookBundle\Formats;

use Koalamon\Bundle\IncidentDashboardBundle\Entity\Event;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\RawEvent;
use Symfony\Component\HttpFoundation\Request;

class JenkinsFormat implements Format
{
    const STATUS_SUCCESS = "SUCCESS";
    const PHASE_COMPLETED = "COMPLETED";

    public function createEvent(Request $request, $payload)
    {
        $newEvent = json_decode($payload);

        $event = new RawEvent();

        if ($newEvent->build->phase != self::PHASE_COMPLETED) {
            return false;
        }

        if ($newEvent->build->status == self::STATUS_SUCCESS) {
            $status = Event::STATUS_SUCCESS;
            $message = "";
        } else {
            $status = Event::STATUS_FAILURE;
            $message = "Jenkins \"" . $newEvent->name . "\" failed";
        }

        $event->setMessage($message);
        $event->setStatus($status);
        $event->setSystem($newEvent->name);
        $event->setIdentifier($newEvent->name);
        $event->setUrl($newEvent->build->full_url);

        $event->setType($event->getIdentifier());

        return $event;
    }
}
