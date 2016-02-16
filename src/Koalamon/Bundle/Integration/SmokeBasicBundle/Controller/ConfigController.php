<?php

namespace Koalamon\Bundle\Integration\SmokeBasicBundle\Controller;

use Koalamon\Bundle\IncidentDashboardBundle\Entity\System;
use Koalamon\Bundle\Integration\KoalaPingBundle\Entity\KoalaPingConfig;
use Koalamon\Bundle\Integration\KoalaPingBundle\Entity\KoalaPingSystem;
use Koalamon\Bundle\Integration\SmokeBundle\Controller\LittleSeoController;
use Koalamon\Bundle\IntegrationBundle\Controller\SystemAwareIntegrationController;
use Koalamon\Bundle\IntegrationBundle\Entity\IntegrationConfig;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


class ConfigController extends SystemAwareIntegrationController
{
    protected function getIntegrationIdentifier()
    {
        return DefaultController::INTEGRATION_ID;
    }

    protected function getApiKey()
    {
        return DefaultController::API_KEY;
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
            $activeSystems = $this->getActiveSystemsForProject($project);
            foreach ($activeSystems as $activeSystem) {
                $projectUrls[] = $this->generateUrl('koalamon_integration_smoke_basic_config_project', ['project' => $project->getIdentifier(), 'system' => $activeSystem[0]['system']->getId()], true) . '?api_key=' . $project->getApiKey();
            }

        }

        return new JsonResponse($projectUrls);
    }

    public function projectAction(Request $request, System $system)
    {
        $this->assertApiKey($request->get('api_key'));

        $littleSeoActiveSystems = $this->getActiveSystemsForProject($this->getProject());

        $filters = array();
        foreach ($littleSeoActiveSystems as $key => $activeSystem) {
            if (is_array($activeSystem[0]['options']['filter'])) {
                foreach ($activeSystem[0]['options']['filter'] as $filter) {
                    $rule = $filter['rule'];
                    $url = $filter['url'];
                    if (!array_key_exists($rule, $filters)) {
                        $filters[$rule] = array();
                    }
                    $filters[$rule][] = $url;
                }
            }
        }

        return $this->render('KoalamonIntegrationSmokeBasicBundle:Config:smoke.yml.twig',
            [
                'littleSeoActiveSystems' => $littleSeoActiveSystems,
                'systems' => $systems,
                'filters' => $filters,
                'system' => $system
            ]);
    }
}
