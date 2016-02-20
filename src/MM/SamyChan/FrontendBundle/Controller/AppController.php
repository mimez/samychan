<?php

namespace MM\SamyChan\FrontendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MM\SamyChan\BackendBundle\Entity;

class AppController extends Controller
{
    public function indexAction($hash)
    {
        // load scmPackage
        $em = $this->get('doctrine');
        $scmPackage = $em->getRepository('MM\SamyChan\BackendBundle\Entity\ScmPackage')->findOneBy(array('hash' => $hash));

        return $this->render('MMSamyChanFrontendBundle:Application:application.html.twig', [
            'hash' => $hash,
            'firstScmFileId' => $this->helperGetFirstFileIdByScmPackage($scmPackage),
            'series' => $scmPackage->getSeries()
        ]);
    }

    protected function helperGetFirstFileIdByScmPackage(Entity\ScmPackage $scmPackage)
    {
        foreach ($scmPackage->getFiles() as $scmFile) {
            if (count($scmFile->getChannels())) {
                return $scmFile->getScmFileId();
            }
        }

        return false;
    }
}
