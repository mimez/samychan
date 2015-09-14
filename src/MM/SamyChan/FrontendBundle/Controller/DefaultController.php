<?php

namespace MM\SamyChan\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($hash)
    {
        return $this->render('MMSamyChanFrontendBundle:Application:application.html.twig');
    }
}
