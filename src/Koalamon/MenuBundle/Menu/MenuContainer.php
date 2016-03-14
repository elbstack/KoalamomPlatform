<?php

namespace Koalamon\MenuBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\InactiveScopeException;

class MenuContainer
{
    const USE_ROUTE_PARAMETER = '@route';

    /*
     * @var FactoryInterface
     */
    private $factory;
    private $request;
    private $menues = [];

    /**
     * @param FactoryInterface $factory
     */
    public function __construct(FactoryInterface $factory, Container $container)
    {
        $this->factory = $factory;

        try {
            $this->request = $container->get('request');
        } catch (InactiveScopeException $e) {
            // this exception is thrown when using the command line
        }
    }

    private function processRouteParameters(array $elementOptions)
    {
        if (array_key_exists('routeParameters', $elementOptions)) {
            foreach ($elementOptions['routeParameters'] as $parameterKey => $parameterValue) {
                if ($parameterValue == self::USE_ROUTE_PARAMETER) {
                    $routeParams = $this->request->attributes->get('_route_params');
                    if ($routeParams && array_key_exists($parameterKey, $routeParams)) {
                        $elementOptions['routeParameters'][$parameterKey] = $routeParams[$parameterKey];
                    }
                }
            }
        }
        return $elementOptions;
    }

    public function addItem($menuName, $elementIdentifier, $elementName, array $elementOptions, $parentIdentifier = null)
    {
        $elementOptions = $this->processRouteParameters($elementOptions);

        if (array_key_exists($menuName, $this->menues)) {
            $menu = $this->menues[$menuName];
        } else {
            $menu = $this->factory->createItem('root');
        }

        $elementOptions['label'] = $elementName;

        if ($parentIdentifier) {
            $this->menues[$menuName][$parentIdentifier]->addChild($elementIdentifier, $elementOptions);
        } else {
            $menu->addChild($elementIdentifier, $elementOptions);
            $this->menues[$menuName] = $menu;
        }
    }

    /**
     * @param string $menuName
     * @return ItemInterface
     */
    public function getMenu($menuName)
    {
        if (!array_key_exists($menuName, $this->menues)) {
            throw new \InvalidArgumentException('No menu with name "' . $menuName . '" found.');
        }
        return $this->menues[$menuName];
    }
}
