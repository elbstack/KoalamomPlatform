<?php

namespace Koalamon\Bundle\Bundle\Integration\GooglePageSpeedBundle\EventListener;

use Koalamon\Bundle\Bundle\IntegrationBundle\EventListener\IntegrationInitEvent;
use Koalamon\Bundle\Bundle\IntegrationBundle\Integration\Integration;
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
        $url = $this->router->generate('koalamon_integration_google_page_speed_homepage', ['project' => $event->getProject()->getIdentifier()]);
        $integrationContainer->addIntegration(new Integration('Google Page Speed', '/images/integrations/pagespeed.png', 'Check the google page speed score.', $url));
    }
}