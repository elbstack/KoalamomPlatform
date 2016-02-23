<?php

namespace Koalamon\HeartbeatBundle\Controller;

use Koalamon\Bundle\IncidentDashboardBundle\Controller\ProjectAwareController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends ProjectAwareController
{
    public function indexAction()
    {
        return $this->render('KoalamonHeartbeatBundle:Default:index.html.twig');
    }

    public function storeAction(Request $request)
    {
        $tools = $request->get('tools');

        $projectTools = $this->getDoctrine()->getRepository('KoalamonIncidentDashboardBundle:Tool')->findBy(['project' => $this->getProject()]);

        $em = $this->getDoctrine()->getManager();

        foreach($projectTools as $projectTool) {
            if(array_key_exists($projectTool->getIdentifier(), $tools)) {
                $projectTool->setInterval((int)$tools[$projectTool->getIdentifier()]);
                $em->persist($projectTool);
            }
        }

        $em->flush();
        return new JsonResponse(['status' => 'success', 'message' => 'Intervals stored successfully.']);
    }
}
