<?php

namespace Koalamon\MenuBundle\EventListener;

use Koalamon\MenuBundle\Menu\MenuBuilder;
use Koalamon\MenuBundle\Menu\MenuContainer;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Yaml\Yaml;

class KernelRequestListener
{
    const MENU_CONFIG_FILE = '/Resources/config/menu.yml';

    private $menuContainer;
    private $configs = [];

    public function __construct(MenuContainer $menuContainer, Container $container)
    {
        $this->menuContainer = $menuContainer;
        $this->initMenus($container);
    }

    private function initMenus(Container $container)
    {
        // @todo check for dev controller
        // @todo check if cached

        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            $menuConfigFileName = dirname($reflection->getFileName()) . self::MENU_CONFIG_FILE;

            if (file_exists($menuConfigFileName)) {
                $this->configs[] = ['content' => $this->getConfig($menuConfigFileName), 'file' => $menuConfigFileName];
            }
        }
    }

    private function getConfig($filename)
    {
        $config = Yaml::parse(file_get_contents($filename))['menu'];
        return $config;
    }

    public function onKernelRequest()
    {
        foreach ($this->configs as $config) {
            foreach ($config['content'] as $menuName => $menuItems) {
                foreach ($menuItems as $menuItemIdentifier => $menuItem) {

                    if (is_string($menuItem)) {
                        throw new \RuntimeException('Unable to parse menu.yml file ("' . $config['filename'] . '"). Seems like one level of hierachy is missing.');
                    }

                    if (!array_key_exists('label', $menuItem)) {
                        $label = $menuItemIdentifier;
                    } else {
                        $label = $menuItem['label'];
                    }

                    if (array_key_exists('parent', $menuItem)) {
                        $parentIdentifier = $menuItem['parent'];
                    } else {
                        $parentIdentifier = null;
                    }

                    if (array_key_exists('options', $menuItem)) {
                        if (!is_array($menuItem['options'])) {
                            throw new \RuntimeException('Unable to parse menu.yml file ("' . $config['filename'] . '"). The "options" parameter must be an array.');
                        }
                        $options = $menuItem['options'];
                    } else {
                        $options = array();
                    }

                    $this->menuContainer->addItem(
                        $menuName,
                        $menuItemIdentifier,
                        $label,
                        $options,
                        $parentIdentifier);
                }
            }
        }
    }
}
