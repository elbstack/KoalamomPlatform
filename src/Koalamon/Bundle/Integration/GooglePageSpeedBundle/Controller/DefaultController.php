<?php

namespace Koalamon\Bundle\Bundle\Integration\GooglePageSpeedBundle\Controller;

use Koalamon\Bundle\IncidentDashboardBundle\Entity\UserRole;
use Koalamon\Bundle\Bundle\IntegrationBundle\Controller\SystemAwareIntegrationController;

class DefaultController extends SystemAwareIntegrationController
{
    const API_KEY = '27010d2a-5617-ad2u-9f0d-993edf547abc';
    const INTEGRATION_ID = 'GooglePageSpeedChecker';

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

        return $this->render('KoalamonIntegrationGooglePageSpeedBundle:Default:index.html.twig',
            [
                'config' => $this->getConfig(),
                'systems' => $this->getSystems(),
                'integratedSystems' => $this->getIntegratedSystems()
            ]);
    }
}
