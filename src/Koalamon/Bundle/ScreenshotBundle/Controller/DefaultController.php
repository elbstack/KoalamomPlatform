<?php

namespace Koalamon\Bundle\ScreenshotBundle\Controller;

use Koalamon\Bundle\IncidentDashboardBundle\Controller\ProjectAwareController;
use Koalamon\Bundle\IncidentDashboardBundle\Entity\System;
use Koalamon\Bundle\ScreenshotBundle\Command\ScreenshotCommand;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends ProjectAwareController
{
    public function getAction($systemIdentifier)
    {
        $system = $this->getDoctrine()->getRepository('KoalamonIncidentDashboardBundle:System')->findOneBy(['identifier' => $systemIdentifier, 'project' => $this->getProject()]);

        if ($system->getImage() == "") {
            throw new NotFoundHttpException('No screenshot found for ' . $system->getName() . " (" . $system->getIdentifier() . ")");
        }

        $webDir = $this->container->getParameter('assetic.write_to');
        $image = $webDir . ScreenshotCommand::IMAGE_DIR . DIRECTORY_SEPARATOR . $system->getImage();

        $headers = array(
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="' . $image . '"');

        return new Response(file_get_contents($image), 200, $headers);
    }
}
