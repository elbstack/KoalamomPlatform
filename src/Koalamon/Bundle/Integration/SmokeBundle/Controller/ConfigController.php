<?php

namespace Koalamon\Bundle\Integration\SmokeBundle\Controller;

use Koalamon\Bundle\Integration\KoalaPingBundle\Entity\KoalaPingConfig;
use Koalamon\Bundle\Integration\KoalaPingBundle\Entity\KoalaPingSystem;
use Koalamon\Bundle\IntegrationBundle\Controller\SystemAwareIntegrationController;
use Koalamon\Bundle\IntegrationBundle\Entity\IntegrationConfig;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


class ConfigController extends SystemAwareIntegrationController
{
    private $rules = array('LittleSeo_Robots' => 'seoReobotsTxt');

    // @todo should be done inside the config file
    const API_KEY = '1785d2a-5617-4da2-9f0d-993edf547522';
    const INTEGRATION_ID = 'Smoke';

    protected function getIntegrationIdentifier()
    {
        return self::INTEGRATION_ID;
    }

    protected function getApiKey()
    {
        return self::API_KEY;
    }

    public function indexAction(Request $request)
    {
        if ($request->get('integration_key') != $this->getApiKey()) {
            return new JsonResponse(['status' => "failure", 'message' => 'Integration key invalid.']);
        }

        $configs = $this->getDoctrine()
            ->getRepository('KoalamonIntegrationBundle:IntegrationConfig')
            ->findBy(['integration' => $this->getIntegrationIdentifier()]);

        $projects = [];

        foreach ($configs as $config) {
            if ($this->getActiveSystemsForProject($config->getProject())) {
                $projects[] = $config->getProject();
            }
        }

        $projectUrls = [];

        foreach ($projects as $project) {
            $projectUrls[] = $this->generateUrl('koalamon_integration_smoke_config_project', ['project' => $project->getIdentifier()], true) . '?api_key=' . $project->getApiKey();
        }

        return new JsonResponse($projectUrls);
    }

    public function projectAction(Request $request)
    {
        $this->assertApiKey($request->get('api_key'));

        return $this->render('KoalamonIntegrationSmokeBundle:Config:smoke.yml.twig',
            [
                'activeSystems' => $this->getActiveSystemsForProject($this->getProject()),
                'systems' => $this->getSystems()
            ]);
    }
}
