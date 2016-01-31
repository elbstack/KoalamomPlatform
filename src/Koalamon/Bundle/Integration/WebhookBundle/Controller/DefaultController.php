<?php

namespace Koalamon\Bundle\Integration\WebhookBundle\Controller;

use Koalamon\Bundle\IncidentDashboardBundle\Controller\ProjectAwareController;

class DefaultController extends ProjectAwareController
{
    public function newRelicAction($hookName)
    {
        return $this->render('KoalamonIntegrationWebhookBundle:Default:' . $hookName . '.html.twig');
    }
}
