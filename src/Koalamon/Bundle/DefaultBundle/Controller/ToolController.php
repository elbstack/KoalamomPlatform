<?php

namespace Koalamon\Bundle\Bundle\DefaultBundle\Controller;

use Koalamon\Bundle\IncidentDashboardBundle\Controller\ProjectAwareController;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\Project;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\System;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\Tool;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\Translation;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\UserRole;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ToolController extends ProjectAwareController
{
    public function adminAction()
    {
        $this->assertUserRights(UserRole::ROLE_ADMIN);
        return $this->render('KoalamonDefaultBundle:Tool:admin.html.twig', array('roles' => UserRole::getRoles()));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function storeAction(Request $request)
    {
        $this->assertUserRights(UserRole::ROLE_ADMIN);

        $tool = $request->get('tool');

        if (array_key_exists("id", $tool)) {
            $toolObject = $this->getDoctrine()
                ->getRepository('KoalamonIncidentDashboardBundle:Tool')
                ->find($tool["id"]);

        } else {
            $toolObject = new Tool();
            $toolObject->setProject($this->getProject());
        }

        if ($tool["identifier"] != "") {
            $toolObject->setIdentifier($tool["identifier"]);
        } else {
            $this->addFlash('notice', 'The parameter "identifier" is required');
            return $this->redirect($this->generateUrl('koalamon_default_tool_admin', ['project' => $this->getProject()->getIdentifier()]));
        }

        if ($tool["name"] != "") {
            $toolObject->setName($tool["name"]);
        } else {
            $this->addFlash('notice', 'The parameter "name" is required');
            return $this->redirect($this->generateUrl('koalamon_default_tool_admin', ['project' => $this->getProject()->getIdentifier()]));
        }

        $toolObject->setDescription($tool["description"]);

        if (array_key_exists('active', $tool)) {
            $toolObject->setActive(true);
        } else {
            $toolObject->setActive(false);
        }

        if (array_key_exists('notify', $tool)) {
            $toolObject->setNotify(true);
        } else {
            $toolObject->setNotify(false);
        }

        if (array_key_exists('systemSpecific', $tool)) {
            $toolObject->setSystemSpecific(true);
        } else {
            $toolObject->setSystemSpecific(false);
        }

        $toolObject->setInterval((int)$tool["interval"]);

        $em = $this->getDoctrine()->getManager();
        $em->persist($toolObject);
        $em->flush();

        $this->addFlash('success', 'Tool "' . $toolObject->getName() . '" successfully saved.');
        return $this->redirect($this->generateUrl('koalamon_default_tool_admin', ['project' => $this->getProject()->getIdentifier()]));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Request $request)
    {
        $this->assertUserRights(UserRole::ROLE_ADMIN);

        $toolObject = $this->getDoctrine()
            ->getRepository('KoalamonIncidentDashboardBundle:Tool')
            ->find($request->get("id"));

        if ($toolObject->hasEventIdentifiers()) {
            $this->addFlash('notice', 'Tool "' . $toolObject->getName() . '" cannot be deleted.');
        } else {
            $em = $this->getDoctrine()->getManager();
            $em->remove($toolObject);
            $em->flush();

            $this->addFlash('success', 'Tool "' . $toolObject->getName() . '" successfully deleted.');
        }
        return $this->redirect($this->generateUrl('koalamon_default_tool_admin', ['project' => $this->getProject()->getIdentifier()]));
    }
}
