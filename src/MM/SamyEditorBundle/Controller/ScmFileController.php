<?php

namespace MM\SamyEditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use MM\SamyEditorBundle\Scm;
use MM\SamyEditorBundle\Entity;

class ScmFileController extends Controller
{
    public function fileAction($hash, $scmFileId)
    {
        $em = $this->get('doctrine');

        // load scmPackage
        $scmPackage = $em->getRepository('MM\SamyEditorBundle\Entity\ScmPackage')->findOneBy(array('hash' => $hash));

        // load scmFile
        $scmFile = $em->getRepository('MM\SamyEditorBundle\Entity\ScmFile')->find($scmFileId);

        // get filemetadata. if not found, its no valid file
        $fileMeta = $this->helperGetFileMetaByName($scmFile->getFilename());
        if (false === $fileMeta) {
            throw new \Exception(sprintf('invalid scm-file=(%s)', $scmFile->getFilename()));
        }

        // check if the requested file-id is allowed for the given hash
        if ($scmFile->getScmPackage() != $scmPackage)
        {
            throw new \Exception('access not allowed');
        }

        // load channels
        $scmChannels = $em->getRepository('MM\SamyEditorBundle\Entity\ScmChannel')->findChannelsByScmFile($scmFile);

        return $this->render('MMSamyEditorBundle:ScmPackage:file.html.twig', array(
            'scmPackage' => $scmPackage,
            'scmFile' => $scmFile,
            'scmChannels' => $scmChannels,
            'scmFileMeta' => $fileMeta,
        ));
    }

    public function fileJsonAction($hash, $scmFileId) {
        $em = $this->get('doctrine');

        // load scmPackage
        $scmPackage = $em->getRepository('MM\SamyEditorBundle\Entity\ScmPackage')->findOneBy(array('hash' => $hash));

        // load scmFile
        $scmFile = $em->getRepository('MM\SamyEditorBundle\Entity\ScmFile')->find($scmFileId);

        // check if the requested file-id is allowed for the given hash
        if ($scmFile->getScmPackage() != $scmPackage)
        {
            throw new \Exception('access not allowed');
        }

        // load channels
        $scmChannels = $em->getRepository('MM\SamyEditorBundle\Entity\ScmChannel')->findChannelsByScmFile($scmFile);

        $data = array();
        foreach ($scmChannels as $scmChannel) {
            $data[] = array(
                'channelId' => $scmChannel->getScmChannelId(),
                'channelNo' => $scmChannel->getChannelNo(),
                'name' => $scmChannel->getName(),
            );
        }
        // json response
        $response = new Response(json_encode(array('data' => $data)));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function fileReorderAction($hash, $scmFileId) {
        $em = $this->get('doctrine');

        // load scmPackage
        $scmPackage = $em->getRepository('MM\SamyEditorBundle\Entity\ScmPackage')->findOneBy(array('hash' => $hash));

        // load scmFile
        $scmFile = $em->getRepository('MM\SamyEditorBundle\Entity\ScmFile')->find($scmFileId);

        // check if the requested file-id is allowed for the given hash
        if ($scmFile->getScmPackage() != $scmPackage)
        {
            throw new \Exception('access not allowed');
        }

        // reorder channels
        $scmOrderer = $this->get('mm_samy_editor.scm_orderer');
        $scmOrderer->reorderChannelsGapless($scmFile);

        // json response
        $response = new Response(json_encode(array('State' => 'OK')));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
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

            $file = $this->helperGetFileMetaByName($scmFile->getFilename());

            // if the scmFile is not supported, we dont display it in the sidebar
            if (false === $file) {
                continue;
            }

            // supported scmFile, go on and generate the nav item
            $navitem = $file;
            $navitem['path'] = $this->generateUrl('mm_samy_editor_scm_file', array(
                'hash' => $scmPackage->getHash(),
                'scmFileId' => $scmFile->getScmFileId(),
            ));

            $navitems[] = $navitem;
        }

        return $this->render('MMSamyEditorBundle:ScmPackage:sidebar.html.twig', array(
            'navitems' => $navitems
        ));
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
            'map-SateD' => array(
                'label' => 'Satelite Digital',
                'icon' => 'fa-globe',
            ),
            'map-AstraHDPlusD' => array(
                'label' => 'AstraHDPlus Digital',
                'icon' => 'fa-globe',
            ),

        );

        if (!isset($supportedFiles[$filename])) {
            return false;
        }

        return $supportedFiles[$filename];
    }
}