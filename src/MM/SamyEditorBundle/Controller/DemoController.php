<?php

namespace MM\SamyEditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DemoController extends Controller
{
    public function loadAction()
    {
        // path to demo file
        $demofilePath = $this->get('kernel')->locateResource('@MMSamyEditorBundle/Resources/demopackage/demo.zip');

        // parse demo file
        $scmPackage = $this->get('mm_samy_editor.scm_parser')->load(new \SplFileObject($demofilePath));
        $scmPackage->setFilename('Channel_list_T-HKPDEUC-1217.0.zip');
        $scmPackage->setDemo(true);

        // save to db
        $em = $this->get('doctrine')->getManager();
        $em->persist($scmPackage);
        $em->flush();

        // create redirect response
        $response = new RedirectResponse($this->generateUrl('mm_samy_editor_scm_package', array('hash' => $scmPackage->getHash())));

        // add scm-package to recent packages (via COOKIES)
        $this->get('mm_samy_editor.scm_recent_manager')->addScmPackage($scmPackage, $response);

        return $response;
    }
}
