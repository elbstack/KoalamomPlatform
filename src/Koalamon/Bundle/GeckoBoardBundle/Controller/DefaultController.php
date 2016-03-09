<?php

namespace Koalamon\Bundle\GeckoBoardBundle\Controller;

use Koalamon\Bundle\IncidentDashboardBundle\Controller\ProjectAwareController;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends ProjectAwareController
{
    public function indexAction()
    {
        return $this->render('KoalamonGeckoBoardBundle:Default:index.html.twig');
    }

    public function filterAction(Request $request)
    {
        $toolId = $request->get('tool');
        $systemId = $request->get('system');

        $tool = $this->getDoctrine()->getRepository('KoalamonIncidentDashboardBundle:Tool')->find($toolId);
        $system = $this->getDoctrine()->getRepository('KoalamonIncidentDashboardBundle:System')->find($systemId);

        $eventIdentifiers = $this->getDoctrine()
            ->getRepository('KoalamonIncidentDashboardBundle:EventIdentifier')
            ->findBy(['system' => $system, 'tool' => $tool]);

        return $this->render('KoalamonGeckoBoardBundle:Default:index.html.twig',
            [
                'system' => $system,
                'tool' => $tool,
                'eventIdentifiers' => $eventIdentifiers
            ]);
    }
}
