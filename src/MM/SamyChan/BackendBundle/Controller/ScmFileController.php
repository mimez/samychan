<?php

namespace MM\SamyChan\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use MM\SamyChan\BackendBundle\Scm;
use MM\SamyChan\BackendBundle\Entity;

class ScmFileController extends Controller
{
    public function fileAction($hash, $scmFileId)
    {
        $em = $this->get('doctrine');

        // load scmFile
        $scmFile = $em->getRepository('MM\SamyChan\BackendBundle\Entity\ScmFile')->findAndValidateHash($scmFileId, $hash);

        // get filemetadata. if not found, its no valid file
        $fileMeta = $this->helperGetFileMetaByName($scmFile->getFilename());
        if (false === $fileMeta) {
            throw new \Exception(sprintf('invalid scm-file=(%s)', $scmFile->getFilename()));
        }

        // load channels
        $scmChannels = $em->getRepository('MM\SamyChan\BackendBundle\Entity\ScmChannel')->findChannelsByScmFile($scmFile);

        // load fields from config
        $config = $this->get('mm_samy_editor.scm_config')->getConfigBySeries($scmFile->getScmPackage()->getSeries());
        $fields = $config[$scmFile->getFilename()]['fields'];

        return $this->render('MMSamyChanBackendBundle:ScmFile:file.html.twig', array(
            'scmPackage' => $scmFile->getScmPackage(),
            'scmFile' => $scmFile,
            'scmChannels' => $scmChannels,
            'scmFileMeta' => $fileMeta,
            'fields' => $fields,
        ));
    }

    public function fileJsonAction($hash, $scmFileId, Request $request) {
        $em = $this->get('doctrine');

        // load scmFile
        $scmFile = $em->getRepository('MM\SamyChan\BackendBundle\Entity\ScmFile')->findAndValidateHash($scmFileId, $hash);

        // load channels
        $scmChannels = $em->getRepository('MM\SamyChan\BackendBundle\Entity\ScmChannel')->findChannelsByScmFile($scmFile);

        // load fields from config
        $config = $this->get('mm_samy_editor.scm_config')->getConfigBySeries($scmFile->getScmPackage()->getSeries());
        $fieldsConfig = $config[$scmFile->getFilename()]['fields'];

        if ('POST' == $request->getMethod()) {
            return $this->handleFileActionPost($request->get('channels'));
        }

        $channels = array();
        foreach ($scmChannels as $scmChannel) {
            $field = array(
                'channelId' => $scmChannel->getScmChannelId(),
                'options' => '',
                'orgChannelNo' => $scmChannel->getChannelNo(),
            );

            foreach ($fieldsConfig as $name => $fieldConfig) {
                $field[$name] = $scmChannel->{'get' . ucfirst($name)}();
            }

            $channels[] = $field;

        }

        $scmFileData = array(
            'scmFileId' => $scmFile->getScmFileId(),
            'filename' => $scmFile->getFilename(),
            'channels' => $channels
        );

        // json response
        $response = new Response(json_encode($scmFileData));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    protected function handleFileActionPost($channels)
    {
        $em = $this->get('doctrine')->getManager();

        $em->getConnection()->beginTransaction();

        foreach ($channels as $scmChannelId => $channel) {
            $scmChannel = $em->getRepository('MM\SamyChan\BackendBundle\Entity\ScmChannel')->find($scmChannelId);
            $scmChannel->setUpdatedAt(new \DateTime());
            $scmChannel->setName($channel['name']);
            $scmChannel->setChannelNo($channel['channelNo']);
            $em->persist($scmChannel);
        }

        $em->flush();
        $em->getConnection()->commit();

        // json response
        $response = new Response(json_encode(array('response' => 'success')));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }


    public function fileReorderAction($hash, $scmFileId) {

        $em = $this->get('doctrine');

        // load scmFile
        $scmFile = $em->getRepository('MM\SamyChan\BackendBundle\Entity\ScmFile')->findAndValidateHash($scmFileId, $hash);

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

        return $this->render('MMSamyChanBackendBundle:ScmPackage:sidebar.html.twig', array(
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