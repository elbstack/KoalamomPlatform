<?php

namespace Koalamon\HeartbeatBundle\EventListener;

use Koalamon\Bundle\DefaultBundle\EventListener\AdminMenuEvent;
use Koalamon\Bundle\DefaultBundle\Menu\Element;
use Koalamon\IntegrationBundle\EventListener\PluginInitEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PluginListener
{
    public function __construct(ContainerInterface $container)
    {
        $this->router = $container->get('Router');
    }

    public function onKoalamonPluginAdminMenuInit(AdminMenuEvent $event)
    {
        $menu = $event->getMenu();
        $project = $event->getProject();

        $menu->addElement(new Element($this->router->generate('koalamon_heartbeat_homepage', ['project' => $project->getIdentifier()], true),
            'Heartbeat', 'menu_admin_heartbeat'));
    }
}