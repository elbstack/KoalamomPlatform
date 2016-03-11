<?php

namespace Koalamon\MenuBundle\EventListener;

use Koalamon\MenuBundle\Menu\MenuBuilder;
use Koalamon\MenuBundle\Menu\MenuContainer;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Yaml\Yaml;

class KernelRequestListener
{
    private $menuContainer;
    private $container;

    public function __construct(MenuContainer $menuContainer, Container $container)
    {
        $this->menuContainer = $menuContainer;
        $this->container = $container;
    }

    private function getConfig($filename)
    {
        $config = Yaml::parse(file_get_contents($filename))['menu'];

        return $config;
    }

    public function onKernelRequest()
    {
        $bundles = $this->container->getParameter('kernel.bundles');

        // check for dev controller

        // check if cached

        foreach ($bundles as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            $menuConfigFileName = dirname($reflection->getFileName()) . '/Resources/config/menu.yml';

            if (file_exists($menuConfigFileName)) {
                $menuConfig = $this->getConfig($menuConfigFileName);

                foreach ($menuConfig as $menuName => $menuItems) {
                    foreach ($menuItems as $menuItemIdentifier => $menuItem) {

                        if (is_string($menuItem)) {
                            throw new \RuntimeException('Unable to parse menu.yml file ("' . $menuConfigFileName . '"). Seems like one level of hierachy is missing.');
                        }

                        $projectAware = array_key_exists('projectAware', $menuItem) && $menuItem['projectAware'] == true;
                        if (array_key_exists('parent', $menuItem)) {
                            $parentIdentifier = $menuItem['parent'];
                        } else {
                            $parentIdentifier = null;
                        }

                        $attributes = [];
                        if (array_key_exists('attributes', $menuItem)) {
                            $attributes = $menuItem['attributes'];
                        }

                        $routeParameters = [];
                        if (array_key_exists('routeParameters', $menuItem)) {
                            $routeParameters = $menuItem['routeParameters'];
                        }

                        $this->menuContainer->addItem(
                            $menuName,
                            $menuItemIdentifier,
                            $menuItem['name'],
                            ['route' => $menuItem['route'], 'routeParameters' => $routeParameters],
                            $attributes,
                            $projectAware,
                            $parentIdentifier);
                    }
                }
            }
        }
    }
}
