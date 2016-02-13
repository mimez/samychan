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
            if ($this->helperGetFileMetaByName($scmFile->getFilename())) {
                return $scmFile->getScmFileId();
            }
        }

        return false;
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
            'dvbt' => array(
                'label' => 'Terrestrial Digital',
                'icon' => 'fa-signal',
            ),
            'dvbs' => array(
                'label' => 'Satelite Digital',
                'icon' => 'fa-globe',
            ),
            'astra_192e' => array(
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
