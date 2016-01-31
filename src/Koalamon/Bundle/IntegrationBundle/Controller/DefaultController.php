<?php

namespace Koalamon\Bundle\IntegrationBundle\Controller;

use Koalamon\Bundle\IncidentDashboardBundle\Controller\ProjectAwareController;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\UserRole;
use Koalamon\Bundle\IntegrationBundle\EventListener\IntegrationInitEvent;
use Koalamon\Bundle\IntegrationBundle\Integration\IntegrationContainer;

class DefaultController extends ProjectAwareController
{
    public function indexAction()
    {
        $this->assertUserRights(UserRole::ROLE_ADMIN);

        $dispatcher = $this->get('event_dispatcher');

        $integrationContainer = new IntegrationContainer();

        $dispatcher->dispatch('koalamon.integration.init', new IntegrationInitEvent($integrationContainer, $this->getProject()));

        return $this->render('KoalamonIntegrationBundle:Default:index.html.twig', ['integrationContainer' => $integrationContainer]);
    }
}