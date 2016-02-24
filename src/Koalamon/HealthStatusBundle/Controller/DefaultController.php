<?php

namespace Koalamon\HealthStatusBundle\Controller;

use Koalamon\Bundle\IncidentDashboardBundle\Controller\ProjectAwareController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends ProjectAwareController
{
    public function indexAction()
    {
        return $this->render('KoalamonHealthStatusBundle:Default:index.html.twig');
    }

    public function storeScoreAction(Request $request)
    {
        $tools = $request->get('tools');

        $projectTools = $this->getDoctrine()->getRepository('KoalamonIncidentDashboardBundle:Tool')->findBy(['project' => $this->getProject()]);

        $em = $this->getDoctrine()->getManager();

        foreach($projectTools as $projectTool) {
            if(array_key_exists($projectTool->getIdentifier(), $tools)) {
                $projectTool->setScore((int)$tools[$projectTool->getIdentifier()]);
                $em->persist($projectTool);
            }
        }

        $em->flush();
        return new JsonResponse(['status' => 'success', 'message' => 'Scores stored successfully.']);
    }


    public function storeThresholdsAction(Request $request)
    {
        $systems = $request->get('systems');

        $projectSystems = $this->getDoctrine()->getRepository('KoalamonIncidentDashboardBundle:System')->findBy(['project' => $this->getProject()]);

        $em = $this->getDoctrine()->getManager();

        foreach($projectSystems as $projectSystem) {
            if(array_key_exists($projectSystem->getIdentifier(), $systems)) {
                $projectSystem->setThreshold((int)$systems[$projectSystem->getIdentifier()]);
                $em->persist($projectSystem);
            }
        }

        $em->flush();
        return new JsonResponse(['status' => 'success', 'message' => 'Thresholds stored successfully.']);
    }
}
