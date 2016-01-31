<?php

namespace Koalamon\Bundle\RestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('KoalamonRestBundle:Default:index.html.twig', array('name' => $name));
    }
}
