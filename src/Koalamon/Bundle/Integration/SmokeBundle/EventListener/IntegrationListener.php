<?php

namespace Koalamon\Bundle\Integration\SmokeBundle\EventListener;

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
        $url = $this->router->generate('koalamon_integration_smoke_seo_homepage', ['project' => $event->getProject()->getIdentifier()]);
        $integrationContainer->addIntegration(new Integration('LittleSEO', '/images/integrations/littleseo.png', 'Checking some search engine rules', $url));
    }
}