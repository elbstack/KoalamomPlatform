<?php

namespace Koalamon\Bundle\Integration\SmokeBundle\Controller;

use Koalamon\Bundle\Integration\KoalaPingBundle\Entity\KoalaPingConfig;
use Koalamon\Bundle\Integration\KoalaPingBundle\Entity\KoalaPingSystem;
use Koalamon\Bundle\IntegrationBundle\Controller\SystemAwareIntegrationController;
use Koalamon\Bundle\IntegrationBundle\Entity\IntegrationConfig;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;


class ConfigController extends SystemAwareIntegrationController
{
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

    private function getActiveLittleSeoConfig()
    {
        $littleSeoSystems = $this->getActiveSystemsForProject($this->getProject(), LittleSeoController::INTEGRATION_ID);

        $rule = ['class' => 'whm\Smoke\Rules\Seo\RobotsDisallowAllRule'];

        foreach ($littleSeoSystems as $littleSeoSystem) {
            if ($littleSeoSystem[0]['options']['seoReobotsTxt'] == "on") {
                $activeSystems[] = $littleSeoSystem[0]['system'];
            }
        }

        return ['LittleSeo_Robots' => ['systems' => $activeSystems, 'rule' => $rule]];
    }

    private function getJsonValidatorConfig()
    {
        $jsonSystems = $this->getActiveSystemsForProject($this->getProject(), JsonValidatorController::INTEGRATION_ID);

        $rule = ['class' => 'whm\Smoke\Rules\Json\ValidRule'];

        $activeSystems = [];

        foreach ($jsonSystems as $jsonSystem) {
            $activeSystems[] = $jsonSystem[0]['system'];
        }

        return ['JsonValidator_Default' => ['systems' => $activeSystems, 'rule' => $rule]];
    }

    /**
     * @return array
     */
    private function getXPathConfig()
    {
        $xpathSystems = $this->getActiveSystemsForProject($this->getProject(), XPathCheckerController::INTEGRATION_ID);

        $activeSystems = [];
        $rules[] = [];

        foreach ($xpathSystems as $xpathSystem) {
            $system = $xpathSystem[0];
            $identifier = 'XPathChecker_' . $system['system']->getId();

            $xpaths = $system['options']['xpaths'];

            if (is_null($xpaths)) {
                $xpathRules = [];
            } else {
                $xpathRules = array_values($xpaths);
            }
            $rule = ['class' => 'whm\Smoke\Rules\Html\XPathExistsRule', 'parameters' => ['xPaths' => $xpathRules]];

            $activeSystems[$identifier] = ['systems' => [$system['system']], 'rule' => $rule];
        }

        return $activeSystems;
    }

    private function extractSystems($activeSystems)
    {
        $allSystems = [];

        foreach ($activeSystems as $rule => $config) {
            foreach ($config['systems'] as $system) {
                $allSystems[$system->getIdentifier()][$system->getUrl()] = $system->getUrl();
            }
        }
        return $allSystems;
    }

    public function projectAction(Request $request)
    {
        $this->assertApiKey($request->get('api_key'));

        $activeSystems = $this->getActiveLittleSeoConfig();
        $activeSystems = array_merge($activeSystems, $this->getXPathConfig());
        $activeSystems = array_merge($activeSystems, $this->getJsonValidatorConfig());

        $rules = ['rules' => []];
        foreach ($activeSystems as $key => $activeSystem) {
            $rules['rules'][$key] = $activeSystem['rule'];
        }

        return $this->render('KoalamonIntegrationSmokeBundle:Config:smoke.yml.twig',
            [
                'activeSystems' => $activeSystems,
                'rules' => Yaml::dump($rules, 5, 2),
                'allSystems' => $this->extractSystems($activeSystems)
            ]);
    }
}
