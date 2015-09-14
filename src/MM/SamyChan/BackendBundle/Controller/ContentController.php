<?php

namespace MM\SamyChan\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use MM\SamyChan\BackendBundle\Scm;
use MM\SamyChan\BackendBundle\Entity;

class ContentController extends Controller
{
    public function imprintAction()
    {
        return $this->render('MMSamyChanBackendBundle:Content:imprint.html.twig');
    }

    public function exportscmAction()
    {
        return $this->render('MMSamyChanBackendBundle:Content:exportscm.html.twig');
    }
}