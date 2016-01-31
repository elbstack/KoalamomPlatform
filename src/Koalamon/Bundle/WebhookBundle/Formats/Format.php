<?php

namespace Koalamon\Bundle\WebhookBundle\Formats;

use Symfony\Component\HttpFoundation\Request;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\RawEvent;

interface Format
{
    /**
     * Returns an Event object created from the request
     *
     * @param Request $request
     * @param string $payload
     * @return RawEvent
     */
    public function createEvent(Request $request, $payload);
}