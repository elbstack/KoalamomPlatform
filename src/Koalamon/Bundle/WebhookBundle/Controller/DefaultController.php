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

        $em = $this->getDoctrine()->getManager();

        ProjectHelper::addRawEvent($this->get("Router"), $em, $rawEvent, $project, $this->get('event_dispatcher'));

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

    private function getProject($apiKey)
    {
        $project = $this->getDoctrine()
            ->getRepository('KoalamonIncidentDashboardBundle:Project')
            ->findOneBy(array("apiKey" => $apiKey));

        return $project;
    }
}
