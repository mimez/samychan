<?php

namespace MM\SamyEditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use MM\SamyEditorBundle\Scm;
use MM\SamyEditorBundle\Entity;

class ContentController extends Controller
{
    public function imprintAction()
    {
        return $this->render('MMSamyEditorBundle:Content:imprint.html.twig');
    }
}