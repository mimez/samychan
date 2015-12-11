<?php

namespace MM\SamyChan\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use MM\SamyChan\BackendBundle\Scm;
use MM\SamyChan\BackendBundle\Entity;
use Symfony\Component\HttpFoundation\JsonResponse;

class UploadController extends Controller
{
    public function uploadAction(Request $request)
    {
        $form = $this->helperBuildUploadForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $scmPackage = $this->processUpload($form);

                $response = new RedirectResponse($this->generateUrl('mm_samychan_frontend_package', array('hash' => $scmPackage->getHash())));

                // add scm-package to recent packages (via COOKIES)
                $this->get('mm_samy_editor.scm_recent_manager')->addScmPackage($scmPackage, $response);

                // create redirect response
                return $response;

            } catch (\Exception $e) {
                $request->getSession()->getFlashBag()->add(
                    'error',
                    $e->getMessage()
                );
            }
        }

        return $this->render('MMSamyChanFrontendBundle:Default:uploadscm.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function uploadJsonAction(Request $request)
    {
        $data = [];

        $form = $this->helperBuildUploadForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $scmPackage = $this->processUpload($form);
                $data['scmPackage'] = ['hash' => $scmPackage->getHash()];

                return new JsonResponse($data);
            } catch (\Exception $e) {
                $data['messages'][] = $e->getMessage();
            }
        }

        return new JsonResponse($data, 400);
    }

    protected function processUpload($form)
    {
        $data = $form->getData();

        $em = $this->get('doctrine')->getManager();

        if (!is_a($data['file'], 'Symfony\Component\HttpFoundation\File\UploadedFile')) {
            throw new \InvalidArgumentException('missing file');
        }

        // parse uploaded file
        $scmPackage = $this->get('mm_samy_editor.scm_parser')->load(
            $data['file']->openFile(), // the file itself
            (isset($data['series']) && $data['series'] != 'auto' ? $data['series'] : null) // auto detection?
        );

        $scmPackage->setFilename($data['file']->getClientOriginalName());
        $em->persist($scmPackage);
        $em->flush();

        return $scmPackage;
    }

    protected function helperBuildUploadForm()
    {
        // get supported series
        $supportedSeries = $this->get('mm_samy_editor.scm_config')->getSupportedSeries();
        $seriesSelector = array('auto' => 'Auto');
        foreach ($supportedSeries as $series) {
            $seriesSelector[$series] = $series . '-Series';
        }

        $defaultData = array();
        $form = $this->createFormBuilder($defaultData, array('csrf_protection' => false))
            ->add('file', 'file')
            ->add('series', 'choice', array('choices' => $seriesSelector, 'required' => false))
            ->add('send', 'submit')
            ->getForm();

        return $form;
    }
}