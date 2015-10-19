<?php

namespace MM\SamyChan\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use MM\SamyChan\BackendBundle\Scm;
use MM\SamyChan\BackendBundle\Entity;
use Symfony\Component\HttpFoundation\JsonResponse;

class ScmPackageController extends Controller
{
    public function uploadAction(Request $request)
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

        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                $data = $form->getData();

                $em = $this->get('doctrine')->getManager();

                // parse uploaded file
                $scmPackage = $this->get('mm_samy_editor.scm_parser')->load(
                    $data['file']->openFile(), // the file itself
                    (isset($data['series']) && $data['series'] != 'auto' ? $data['series'] : null) // auto detection?
                );

                $scmPackage->setFilename($data['file']->getClientOriginalName());
                $em->persist($scmPackage);
                $em->flush();

                // create redirect response
                $response = new RedirectResponse($this->generateUrl('mm_samychan_frontend_package', array('hash' => $scmPackage->getHash())));

                // add scm-package to recent packages (via COOKIES)
                $this->get('mm_samy_editor.scm_recent_manager')->addScmPackage($scmPackage, $response);

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

    public function indexAction($hash)
    {
        // load scmPackage
        $em = $this->get('doctrine');
        $scmPackage = $em->getRepository('MM\SamyChan\BackendBundle\Entity\ScmPackage')->findOneBy(array('hash' => $hash));

        if (!$scmPackage) {
            throw new \Exception(sprintf('scm-package with hash=(%s) does not exist', $hash));
        }

        $data = array(
            'hash' => $scmPackage->getHash(),
            'scmPackageId' => $scmPackage->getScmPackageId(),
            'filename' => $scmPackage->getFilename(),
            'files' => array(),
            'favorites' => array(),
        );

        foreach ($scmPackage->getFiles() as $scmFile) {

            $file = $this->helperGetFileMetaByName($scmFile->getFilename());

            // if the scmFile is not supported, we dont display it in the sidebar
            if (false === $file) {
                continue;
            }

            $file['scmFileId'] = $scmFile->getScmFileId();
            $file['channelCount'] = $this->getChannelCountByScmFile($scmFile);

            $data['files'][] = $file;
        }

        // add favorites
        // favorites
        for ($i = 1; $i <= 5; $i++) {
            $data['favorites'][] = array(
                'favNo' => $i,
                'channelCount' => $this->getFavoritesCountByScmPackage($scmPackage, $i)
            );
        }

        $response = new JsonResponse();
        $response->setData($data);

        return $response;
    }

    public function downloadAction($hash)
    {
        $em = $this->get('doctrine');
        $scmPackage = $em->getRepository('MM\SamyChan\BackendBundle\Entity\ScmPackage')->findOneBy(array('hash' => $hash));

        // pack the scm package
        $scmBinaryData = $this->get('mm_samy_editor.scm_packer')->pack($scmPackage);

        header('Content-Type: application/zip');
        header(sprintf('Content-Length: %s', strlen($scmBinaryData)));
        header(sprintf('Content-Disposition: attachment; filename="%s"', $scmPackage->getFilename()));
        echo $scmBinaryData;
        die();
    }

    /**
     * Calculates the channel count of a scmFile
     *
     * @param Entity\ScmFile $scmFile
     * @return integer
     */
    public function getChannelCountByScmFile(Entity\ScmFile $scmFile)
    {
        $em = $this->get('doctrine')->getManager();

        $q = $em->createQuery('SELECT COUNT(c.channelNo) FROM MM\SamyChan\BackendBundle\Entity\ScmChannel c WHERE c.scmFile = :scmFile AND c.channelNo > 0');
        $q->setParameter('scmFile', $scmFile);
        return (int)$q->getSingleScalarResult();
    }

    /**
     * Calculates the channel count of a scmFile
     *
     * @param Entity\ScmFile $scmFile
     * @return integer
     */
    public function getFavoritesCountByScmPackage(Entity\ScmPackage $scmPackage, $favNo)
    {
        $em = $this->get('doctrine')->getManager();

        $q = $em->createQuery("SELECT COUNT(c.channelNo) FROM MM\SamyChan\BackendBundle\Entity\ScmChannel c JOIN c.scmFile f WHERE f.scmPackage = :scmPackage AND c.channelNo > 0 AND c.fav{$favNo}sort > 0");
        $q->setParameter('scmPackage', $scmPackage);
        return (int)$q->getSingleScalarResult();
    }

    /**
     * Helper to get metadata of a filename
     *
     * @param $filename
     * @return bool|array
     */
    public function helperGetFileMetaByName($filename)
    {
        $supportedFiles = array(
            'map-CableD' => array(
                'label' => 'Cable Digital',
                'icon' => 'fa-signal',
            ),
            'map-AirA' => array(
                'label' => 'Terrestrial Analog',
                'icon' => 'fa-globe',
            ),
            'map-AirD' => array(
                'label' => 'Terrestrial Digital',
                'icon' => 'fa-globe',
            ),
            'map-SateD' => array(
                'label' => 'Satelite Digital',
                'icon' => 'fa-globe',
            ),
            'map-AstraHDPlusD' => array(
                'label' => 'AstraHDPlus Digital',
                'icon' => 'fa-globe',
            ),
            'dvbc' => array(
                'label' => 'Cable Digital',
                'icon' => 'fa-signal',
            ),
            'dvbs' => array(
                'label' => 'Satelite Digital',
                'icon' => 'fa-globe',
            ),
        );

        if (!isset($supportedFiles[$filename])) {
            return false;
        }

        return $supportedFiles[$filename];
    }
}