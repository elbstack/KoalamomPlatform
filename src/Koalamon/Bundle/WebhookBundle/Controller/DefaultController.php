<?php

namespace Koalamon\Bundle\WebhookBundle\Controller;

use Koalamon\Bundle\IncidentDashboardBundle\Entity\Event;

use Koalamon\Bundle\IncidentDashboardBundle\Entity\EventIdentifier;
use Koalamon\Bundle\IncidentDashboardBundle\EventListener\NewEventEvent;
use Koalamon\Bundle\IncidentDashboardBundle\Util\ProjectHelper;
use Koalamon\Bundle\WebhookBundle\Formats\AppDynamicsFormat;
use Koalamon\Bundle\WebhookBundle\Formats\DefaultFormat;
use Koalamon\Bundle\WebhookBundle\Formats\FormatHandler;
use Koalamon\Bundle\WebhookBundle\Formats\JenkinsFormat;
use Koalamon\Bundle\WebhookBundle\Formats\JiraFormat;
use Koalamon\Bundle\WebhookBundle\Formats\MonitisFormat;
use Koalamon\Bundle\WebhookBundle\Formats\MonitorUsFormat;
use Koalamon\Bundle\WebhookBundle\Formats\NewRelicFormat;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
    const STATUS_SUCCESS = "success";
    const STATUS_FAILURE = "failure";
    const STATUS_SKIPPED = "skipped";

    private function getJsonRespone($status, $message = "")
    {
        return new JsonResponse(array('status' => $status, 'message' => $message));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function indexAction(Request $request, $formatName = "default")
    {
        $payload = file_get_contents('php://input');

        $content = "queryString: " . $request->getQueryString() . "\n payload: " . $payload;

        $debugDir = "/tmp/koalamon/";
        if (is_dir($debugDir)) {
            file_put_contents($debugDir . "webhook_" . $formatName . ".log", json_encode($content));
        }

        $project = $this->getProject($request->get("api_key"));

        if ($project == null) {
            return $this->getJsonRespone(self::STATUS_FAILURE, "No project with api_key " . $request->get("api_key") . ' found.');
        }

        $rawEvent = $this->getFormatHandler()->run($formatName, $request, $payload);

        if ($rawEvent === false) {
            return $this->getJsonRespone(self::STATUS_SKIPPED);
        }

        $event = new Event();

        $event->setStatus($rawEvent->getStatus());
        $event->setMessage($rawEvent->getMessage());
        $event->setSystem($rawEvent->getSystem());
        $event->setType($rawEvent->getType());
        $event->setUnique($rawEvent->isUnique());
        $event->setUrl($rawEvent->getUrl());
        $event->setValue($rawEvent->getValue());

        $em = $this->getDoctrine()->getManager();

        $identifier = $em->getRepository('KoalamonIncidentDashboardBundle:EventIdentifier')
            ->findOneBy(array('project' => $project, 'identifier' => $rawEvent->getIdentifier()));

        if (is_null($identifier)) {
            $identifier = new EventIdentifier();
            $identifier->setProject($project);
            $identifier->setIdentifier($rawEvent->getIdentifier());

            $em->persist($identifier);
            $em->flush();
        }

        $event->setEventIdentifier($identifier);

        $translatedEvent = $this->translate($event);

        ProjectHelper::addEvent($this->get("Router"), $em, $translatedEvent, $this->get('event_dispatcher'));

        return $this->getJsonRespone(self::STATUS_SUCCESS);
    }

    public function formatAction(Request $request, $format)
    {
        return $this->indexAction($request, $format);
    }

    private function getFormatHandler()
    {
        $formatHandler = new FormatHandler();

        $formatHandler->addFormat("default", new DefaultFormat());
        $formatHandler->addFormat("newrelic", new NewRelicFormat());
        $formatHandler->addFormat("jenkins", new JenkinsFormat());
        $formatHandler->addFormat("jira", new JiraFormat());
        $formatHandler->addFormat("appdynamics", new AppDynamicsFormat());
        $formatHandler->addFormat("monitis", new MonitisFormat());
        $formatHandler->addFormat("monitorus", new MonitorUsFormat());

        return $formatHandler;
    }

    private function translate(Event $event)
    {
        $translations = $this->getDoctrine()
            ->getRepository('KoalamonIncidentDashboardBundle:Translation')
            ->findBy(array('project' => $event->getEventIdentifier()->getProject()));

        foreach ($translations as $translation) {
            if (preg_match('^' . $translation->getIdentifier() . '^', $event->getEventIdentifier()->getIdentifier())) {
                return $translation->translate($event);
            }
        }

        return $event;
    }

    private function getProject($apiKey)
    {
        $project = $this->getDoctrine()
            ->getRepository('KoalamonIncidentDashboardBundle:Project')
            ->findOneBy(array("apiKey" => $apiKey));

        return $project;
    }
}
