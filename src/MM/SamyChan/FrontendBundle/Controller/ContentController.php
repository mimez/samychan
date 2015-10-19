<?php

namespace MM\SamyChan\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MM\SamyChan\BackendBundle\Scm;
use MM\SamyChan\BackendBundle\Entity;

class ContentController extends Controller
{
    public function imprintAction()
    {
        return $this->render('MMSamyChanFrontendBundle:Content:imprint.html.twig');
    }

    public function exportscmAction()
    {
        return $this->render('MMSamyChanFrontendBundle:Content:exportscm.html.twig');
    }
}