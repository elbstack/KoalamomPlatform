<?php

namespace Koalamon\Bundle\Integration\SmokeBasicBundle\EventListener;

use Koalamon\Bundle\IntegrationBundle\EventListener\IntegrationInitEvent;
use Koalamon\Bundle\IntegrationBundle\Integration\Integration;
use Symfony\Component\DependencyInjection\Container;

class IntegrationListener
{
    private $router;

    public function __construct(Container $container)
    {
        $this->router = $container->get('router');
    }

    public function onInit(IntegrationInitEvent $event)
    {
        $integrationContainer = $event->getIntegrationContainer();
        $url = $this->router->generate('koalamon_integration_smoke_basic_homepage', ['project' => $event->getProject()->getIdentifier()]);
        $integrationContainer->addIntegration(new Integration('Smoke', '/images/integrations/smoke.png', 'Crawling your website for anomalies.', $url));
    }
}