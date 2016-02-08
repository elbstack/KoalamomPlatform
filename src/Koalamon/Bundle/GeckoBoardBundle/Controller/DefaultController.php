<?php

namespace Koalamon\Bundle\GeckoBoardBundle\Controller;

use Koalamon\Bundle\IncidentDashboardBundle\Controller\ProjectAwareController;

class DefaultController extends ProjectAwareController
{
    public function indexAction()
    {
        return $this->render('KoalamonGeckoBoardBundle:Default:index.html.twig');
    }
}
