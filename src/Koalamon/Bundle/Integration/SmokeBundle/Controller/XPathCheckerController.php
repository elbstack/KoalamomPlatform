<?php

namespace Koalamon\Bundle\Integration\SmokeBundle\Controller;

use Koalamon\Bundle\IncidentDashboardBundle\Entity\UserRole;
use Koalamon\Bundle\Integration\KoalaPingBundle\Entity\KoalaPingConfig;
use Koalamon\Bundle\Integration\KoalaPingBundle\Entity\KoalaPingSystem;
use Koalamon\Bundle\IntegrationBundle\Controller\SystemAwareIntegrationController;

class XPathCheckerController extends SystemAwareIntegrationController
{
    const INTEGRATION_ID = "xpathchecker";

    protected function getIntegrationIdentifier()
    {
        return self::INTEGRATION_ID;
    }

    protected function getApiKey()
    {
        return ConfigController::API_KEY;
    }

    public function indexAction()
    {
        $this->assertUserRights(UserRole::ROLE_ADMIN);
        return $this->render('KoalamonIntegrationSmokeBundle:XPathChecker:index.html.twig',
            [
                'config' => $this->getConfig(),
                'systems' => $this->getSystems(),
                'integratedSystems' => $this->getIntegratedSystems()
            ]);
    }
}
