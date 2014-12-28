<?php

namespace MM\SamyEditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        $defaultData = array();
        $form = $this->createFormBuilder($defaultData)
            ->add('file', 'file')
            ->add('send', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            // data is an array with "name", "email", and "message" keys
            $data = $form->getData();
            $em = $this->get('doctrine')->getManager();
            $scmArchiveLoader = $this->get('mm_samy_editor.scm_parser');
            $scmPackage = $scmArchiveLoader->load($data['file']->openFile());
            $scmPackage->setFilename($data['file']->getClientOriginalName());
            $em->persist($scmPackage);
            $em->flush();

            return $this->redirectToRoute('mm_samy_editor_scm_package', array('hash' => $scmPackage->getHash()));
        }

        return $this->render('MMSamyEditorBundle:Default:index.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    
}
