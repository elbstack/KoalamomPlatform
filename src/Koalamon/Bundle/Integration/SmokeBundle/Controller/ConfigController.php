<?php

namespace Koalamon\Bundle\Integration\SmokeBundle\Controller;

use Koalamon\Bundle\Integration\KoalaPingBundle\Entity\KoalaPingConfig;
use Koalamon\Bundle\Integration\KoalaPingBundle\Entity\KoalaPingSystem;
use Koalamon\Bundle\IntegrationBundle\Controller\SystemAwareIntegrationController;
use Koalamon\Bundle\IntegrationBundle\Entity\IntegrationConfig;
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
        $this->assertApiKey($request->get('api_key'));

        $activeSystems = array();

        $configs = $this->getDoctrine()
            ->getRepository('KoalamonIntegrationBundle:IntegrationConfig')
            ->findBy(['status' => IntegrationConfig::STATUS_ALL, 'integration' => $this->getIntegrationIdentifier(), 'useSaaS' => true, 'project' => $this->getProject()]);

        foreach ($configs as $config) {
            $systems = $config->getProject()->getSystems();
            foreach ($systems->toArray() as $system) {
                $activeSystems[$system][] = ['system' => $system, 'options' => $config->getOptions()];
            }
        }

        $configs = $this->getDoctrine()
            ->getRepository('KoalamonIntegrationBundle:IntegrationConfig')
            ->findBy(['status' => IntegrationConfig::STATUS_SELECTED, 'integration' => $this->getIntegrationIdentifier(), 'useSaaS' => true, 'project' => $this->getProject()]);

        foreach ($configs as $config) {
            $integrationSystems = $this->getDoctrine()
                ->getRepository('KoalamonIntegrationBundle:IntegrationSystem')
                ->findBy(['project' => $config->getProject(), 'integration' => $this->getIntegrationIdentifier()]);

            foreach ($integrationSystems as $integrationSystem) {
                if ($integrationSystem->getSystem()->getParent()) {
                    $mainSystem = $integrationSystem->getSystem()->getParent()->getIdentifier();
                } else {
                    $mainSystem = $integrationSystem->getSystem()->getIdentifier();
                }
                if (!array_key_exists($mainSystem, $activeSystems)) {
                    $activeSystems[$mainSystem] = [];
                }
                $activeSystems[$mainSystem][] = ['system' => $integrationSystem->getSystem(), 'options' => $integrationSystem->getOptions()];
            }
        }

        return $this->render('KoalamonIntegrationSmokeBundle:Config:smoke.yml.twig',
            [
                'activeSystems' => $activeSystems,
                'systems' => $this->getSystems()
            ]);
    }
}
