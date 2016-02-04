<?php

namespace Koalamon\Bundle\Integration\SmokeBundle\Controller;

use Koalamon\Bundle\IncidentDashboardBundle\Entity\UserRole;
use Koalamon\Bundle\Integration\KoalaPingBundle\Entity\KoalaPingConfig;
use Koalamon\Bundle\Integration\KoalaPingBundle\Entity\KoalaPingSystem;
use Koalamon\Bundle\IntegrationBundle\Controller\SystemAwareIntegrationController;


class LittleSeoController extends SystemAwareIntegrationController
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
    
    public function indexAction()
    {
        $this->assertUserRights(UserRole::ROLE_ADMIN);
        return $this->render('KoalamonIntegrationSmokeBundle:Default:index.html.twig',
            [
                'config' => $this->getConfig(),
                'systems' => $this->getSystems(),
                'integratedSystems' => $this->getIntegratedSystems()
            ]);
    }
}
