<?php

namespace Koalamon\MenuBundle\Twig;

use Koalamon\MenuBundle\Menu\MenuContainer;

class KoalamonMenuExtension extends \Twig_Extension
{
    private $menuContainer;

    const DEFAULT_TEMPLATE = 'KoalamonMenuBundle:Menu:default.html.twig';

    public function __construct(MenuContainer $menuContainer)
    {
        $this->menuContainer = $menuContainer;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('koalamon_menu_render', array($this, 'renderMenu'), array(
                    'is_safe' => array('html'),
                    'needs_environment' => true)
            ));
    }

    public function renderMenu(\Twig_Environment $twig, $menuName, $template = null)
    {
        if (is_null($template)) {
            $template = self::DEFAULT_TEMPLATE;
        }

        $menu = $this->menuContainer->getMenu($menuName);

        return $twig->render($template, ['menu' => $menu]);
    }

    public function getName()
    {
        return "koalamon_menu";
    }
}
