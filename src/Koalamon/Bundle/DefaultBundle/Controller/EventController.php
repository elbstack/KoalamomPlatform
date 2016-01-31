<?php

namespace Koalamon\Bundle\Bundle\DefaultBundle\Controller;

use Koalamon\Bundle\IncidentDashboardBundle\Controller\ProjectAwareController;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\Event;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\EventIdentifier;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\KnownIssue;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\UserRole;

class EventController extends ProjectAwareController
{
    public function markAsKnownIssueAction(EventIdentifier $eventIdentifier)
    {
        $this->setProject($eventIdentifier->getProject());
        $this->assertUserRights(UserRole::ROLE_COLLABORATOR);

        $eventIdentifier->setKnownIssue(true);

        $project = $eventIdentifier->getProject();
        $project->decOpenIncidentCount();

        $em = $this->getDoctrine()->getManager();
        $em->persist($eventIdentifier);
        $em->persist($project);
        $em->flush();

        return $this->redirectToRoute("bauer_incident_dashboard_core_homepage", array('project' => $project->getIdentifier()));
    }

    public function unMarkAsKnownIssueAction(EventIdentifier $eventIdentifier)
    {
        $this->setProject($eventIdentifier->getProject());
        $this->assertUserRights(UserRole::ROLE_COLLABORATOR);

        $eventIdentifier->setKnownIssue(false);
        $project = $eventIdentifier->getProject();
        $project->incOpenIncidentCount();

        $em = $this->getDoctrine()->getManager();
        $em->persist($eventIdentifier);
        $em->persist($project);
        $em->flush();

        return $this->redirectToRoute("bauer_incident_dashboard_core_homepage", array('project' => $project->getIdentifier()));
    }
}
