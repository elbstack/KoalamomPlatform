<?php

namespace Koalamon\MenuBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\InactiveScopeException;

class MenuContainer
{
    private $factory;

    private $currentProject = null;

    private $menues = [];

    private $request;

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

    private function getCurrentProject()
    {
        if (!$this->currentProject) {
            $routeParams = $this->request->attributes->get('_route_params');
            if ($routeParams && array_key_exists('project', $routeParams)) {
                $this->currentProject = $routeParams['project'];
            }
        }
        return $this->currentProject;
    }

    public function addItem($menuName, $elementIdentifier, $elementName, array $elementOptions = array(), array $attributes = array(), $projectAware = true, $parentIdentifier = null)
    {
        if ($projectAware
            && ((!array_key_exists('routeParameters', $elementOptions))
                || (!array_key_exists('project', $elementOptions['routeParameters'])))
        ) {

            $currentProject = $this->getCurrentProject();

            if ($currentProject == "") {
                return;
            }

            $elementOptions['routeParameters']['project'] = $currentProject;
        }

        if (array_key_exists($menuName, $this->menues)) {
            $menu = $this->menues[$menuName];
        } else {
            $menu = $this->factory->createItem('root');
        }

        $elementOptions['label'] = $elementName;

        if ($parentIdentifier) {
            $this->menues[$menuName][$parentIdentifier]->addChild($elementIdentifier, $elementOptions)->setAttributes($attributes);
        } else {
            $menu->addChild($elementIdentifier, $elementOptions)->setAttributes($attributes);
            $this->menues[$menuName] = $menu;
        }

    }

    /**
     * @param $menuName
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
