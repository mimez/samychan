<?php

namespace MM\SamyEditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use MM\SamyEditorBundle\Scm;
use MM\SamyEditorBundle\Entity;

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
                $response = new RedirectResponse($this->generateUrl('mm_samy_editor_scm_package', array('hash' => $scmPackage->getHash())));

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

        return $this->render('MMSamyEditorBundle:Default:uploadscm.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function indexAction($hash)
    {
        $em = $this->get('doctrine');
        $scmPackage = $em->getRepository('MM\SamyEditorBundle\Entity\ScmPackage')->findOneBy(array('hash' => $hash));

        return $this->render('MMSamyEditorBundle:ScmPackage:index.html.twig', array(
            'scmPackage' => $scmPackage,
        ));
    }

    public function downloadAction($hash)
    {
        $em = $this->get('doctrine');
        $scmPackage = $em->getRepository('MM\SamyEditorBundle\Entity\ScmPackage')->findOneBy(array('hash' => $hash));

        // pack the scm package
        $scmBinaryData = $this->get('mm_samy_editor.scm_packer')->pack($scmPackage);

        header('Content-Type: application/zip');
        header(sprintf('Content-Length: %s', strlen($scmBinaryData)));
        header(sprintf('Content-Disposition: attachment; filename="%s"', $scmPackage->getFilename()));
        echo $scmBinaryData;
        die();
    }

    /**
     * Embedded Controller um die Sidebar zu bauen
     *
     * @param Entity\ScmPackage $scmPackage
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sidebarAction(Entity\ScmPackage $scmPackage)
    {
        // Generate Navitems
        $navitems = array();
        foreach ($scmPackage->getFiles() as $scmFile) {

            // get metadata of the file (by its name)
            $file = $this->helperGetFileMetaByName($scmFile->getFilename());

            // if the scmFile is not supported, we dont display it in the sidebar
            if (false === $file) {
                continue;
            }

            // generate the nav item
            $navitem = $file;
            $navitem['path'] = $this->generateUrl('mm_samy_editor_scm_file', array(
                'hash' => $scmPackage->getHash(),
                'scmFileId' => $scmFile->getScmFileId(),
            ));
            $navitem['channelCount'] = $this->getChannelCountByScmFile($scmFile);
            $navitems[] = $navitem;
        }


        // add favorites
        // favorites
        for ($i = 1; $i <= 5; $i++) {
            $navitems[] = array(
                'path' => $this->generateUrl('mm_samy_editor_scm_favorites', array(
                    'hash' => $scmPackage->getHash(),
                    'favNo' => $i
                )),
                'label' => 'Favorites ' . $i,
                'icon' => 'fa-star',
                'channelCount' => $this->getFavoritesCountByScmPackage($scmPackage, $i)
            );
        }

        return $this->render('MMSamyEditorBundle:ScmPackage:sidebar.html.twig', array(
            'navitems' => $navitems
        ));
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

        $q = $em->createQuery('SELECT COUNT(c.channelNo) FROM MM\SamyEditorBundle\Entity\ScmChannel c WHERE c.scmFile = :scmFile AND c.channelNo > 0');
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

        $q = $em->createQuery("SELECT COUNT(c.channelNo) FROM MM\SamyEditorBundle\Entity\ScmChannel c JOIN c.scmFile f WHERE f.scmPackage = :scmPackage AND c.channelNo > 0 AND c.fav{$favNo}sort > 0");
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
        );

        if (!isset($supportedFiles[$filename])) {
            return false;
        }

        return $supportedFiles[$filename];
    }
}