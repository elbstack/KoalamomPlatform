<?php

namespace Koalamon\Bundle\Bundle\DefaultBundle\Controller;

use Koalamon\Bundle\IncidentDashboardBundle\Controller\ProjectAwareController;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\Project;
use Koalamon\Bundle\Bundle\DefaultBundle\EventListener\AdminMenuEvent;
use Koalamon\Bundle\Bundle\DefaultBundle\Menu\Element;
use Koalamon\Bundle\Bundle\DefaultBundle\Menu\Menu;
use Symfony\Component\EventDispatcher\EventDispatcher;

class AdminController extends ProjectAwareController
{
    public function renderMenuAction(Project $project, $currentUri)
    {
        $menu = new Menu();

        $menu->addElement(new Element($this->generateUrl('koalamon_default_project_admin', ['project' => $project->getIdentifier()], true),
            'Project', 'menu_admin_project'));

        $menu->addElement(new Element($this->generateUrl('koalamon_default_user_admin', ['project' => $project->getIdentifier()], true),
            'Collaborators', 'menu_admin_user'));

        $menu->addElement(new Element($this->generateUrl('koalamon_default_system_admin', ['project' => $project->getIdentifier()], true),
            'Systems', 'menu_admin_systems'));

        $menu->addElement(new Element($this->generateUrl('koalamon_default_tool_admin', ['project' => $project->getIdentifier()], true),
            'Tools', 'menu_admin_tools'));

        $menu->addElement(new Element($this->generateUrl('koalamon_default_admin_translation_home', ['project' => $project->getIdentifier()], true),
            'Translations', 'menu_admin_translations'));

        $dispatcher = $this->get('event_dispatcher');
        /** @var EventDispatcher $dispatcher */

        $dispatcher->dispatch('koalamon.plugin.admin.menu.init', new AdminMenuEvent($menu, $project));

        return $this->render('KoalamonDefaultBundle:Admin:menu.html.twig', ['menu' => $menu, 'currentUri' => $currentUri]);
    }
}
