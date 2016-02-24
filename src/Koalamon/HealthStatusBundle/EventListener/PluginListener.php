<?php

namespace Koalamon\HealthStatusBundle\EventListener;

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

        $statusElement = $menu->getElement('menu_admin_systems');

        $statusElement->addSubElement(new Element($this->router->generate('koalamon_health_status_homepage', ['project' => $project->getIdentifier()], true),
            'Health Status', 'menu_admin_health_status'));

        //$menu->addElement($element);
    }
}