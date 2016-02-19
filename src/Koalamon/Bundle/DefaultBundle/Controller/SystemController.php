<?php

namespace Koalamon\Bundle\DefaultBundle\Controller;

use Koalamon\Bundle\IncidentDashboardBundle\Controller\ProjectAwareController;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\System;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\UserRole;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SystemController extends ProjectAwareController
{
    public function adminAction()
    {
        $this->assertUserRights(UserRole::ROLE_ADMIN);

        $systems = $this->getDoctrine()
            ->getRepository('KoalamonIncidentDashboardBundle:System')
            ->findBy(['project' => $this->getProject(), 'parent' => null], ['name' => "ASC"]);

        return $this->render('KoalamonDefaultBundle:System:admin.html.twig', ['systems' => $systems]);
    }

    private function getJsonResponse($status, $message, $elementId, $id = 0, array $data = array())
    {
        $mainData = array_merge(['status' => $status, 'message' => $message, 'id' => $id, 'elementId' => $elementId], $data);
        return new JsonResponse($mainData);
    }

    public function storeSystemAction(Request $request)
    {
        $this->assertUserRights(UserRole::ROLE_ADMIN);

        $system = $request->get('system');

        if (array_key_exists("id", $system) && $system['id'] != '') {
            $systemObject = $this->getDoctrine()
                ->getRepository('KoalamonIncidentDashboardBundle:System')
                ->find($system["id"]);
        } else {
            $systemObject = new System();
            $systemObject->setProject($this->getProject());
        }

        if (array_key_exists("parent", $system)) {
            $parent = $this->getDoctrine()
                ->getRepository('KoalamonIncidentDashboardBundle:System')
                ->find($system["parent"]);

            $systemObject->setParent($parent);
        }

        if (array_key_exists("identifier", $system) && $system["identifier"] != "") {
            $systemObject->setIdentifier($system["identifier"]);
        } else if (!array_key_exists("parent", $system)) {
            return $this->getJsonResponse('failure', 'The parameter "identifier" is required', (int)$system['elementId']);
        }

        if ($system["url"] != "" && !filter_var($system['url'], FILTER_VALIDATE_URL) === false) {
            $systemObject->setUrl($system["url"]);
        } else {
            return $this->getJsonResponse('failure', 'The parameter "URL" requires a valid URL', (int)$system['elementId']);
        }

        if ($system["name"] != "") {
            $systemObject->setName($system["name"]);
        } else {
            $systemObject->setName($system['url']);
        }

        if ($system["description"] != "") {
            $systemObject->setDescription($system["description"]);
        } else {
            $systemObject->setDescription(null);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($systemObject);
        $em->flush();

        return $this->getJsonResponse('success', 'System "' . $systemObject->getName() . '" successfully saved.', (int)$system['elementId'], $systemObject->getId());
    }

    private function removeSystem(System $system)
    {
        $deletedIds = [];

        $em = $this->getDoctrine()->getManager();

        // remove subsystems
        foreach ($system->getSubsystems() as $child) {
            $deletedIds = $this->removeSystem($child);
        }

        // @todo should be done via an event
        // remove integration systems
        $integrationSystems = $this->getDoctrine()->getRepository('KoalamonIntegrationBundle:IntegrationSystem')->findBy(['system' => $system]);
        foreach ($integrationSystems as $integrationSystem) {
            $em->remove($integrationSystem);
        }

        // collected systems
        $collections = $this->getDoctrine()->getRepository('KoalamonIntegrationMissingRequestBundle:Collection')->findBy(['project' => $system->getProject()]);
        foreach ($collections as $collection) {
            $collection->removeSystem($system);
            $em->persist($collection);
            $em->flush();
        }

        $deletedIds[] = $system->getId();

        $em->remove($system);
        $em->flush();

        return $deletedIds;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteSystemAction(Request $request)
    {
        $this->assertUserRights(UserRole::ROLE_ADMIN);

        $systemObject = $this->getDoctrine()
            ->getRepository('KoalamonIncidentDashboardBundle:System')
            ->find($request->get("system_id"));

        $deletedIds = $this->removeSystem($systemObject);

        return $this->getJsonResponse('success', 'System "' . $systemObject->getName() . '" deleted . ', $request->get("system_id"), $request->get("system_id"), ['deletedIds' => $deletedIds]);
    }
}
