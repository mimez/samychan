<?php

namespace MM\SamyChan\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use MM\SamyChan\BackendBundle\Scm;
use MM\SamyChan\BackendBundle\Entity;

class ScmFileController extends Controller
{
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
            'fields' => $fieldsConfig,
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

    public function fileExportAction($hash, $scmFileId)
    {
        $em = $this->get('doctrine');

        // load scmFile
        $scmFile = $em->getRepository('MM\SamyChan\BackendBundle\Entity\ScmFile')->findAndValidateHash($scmFileId, $hash);

        $response = new StreamedResponse();
        $response->setCallback(function () use ($scmFile) {

            $file = new \SplFileObject('php://output', 'w+');

            // Header
            $file->fputcsv([
                'channelNo' => 'Channel no.',
                'name' => 'Name',
            ], ";");

            // channels
            foreach ($scmFile->getChannels() as $channel)
            {
                $file->fputcsv([
                    'channelNo' => $channel->getChannelNo(),
                    'name' => utf8_decode($channel->getName()),
                ], ";");
            }
        });

        // filename = package-filename_channel-list-filename.csv
        $filename = $scmFile->getScmPackage()->getFilename() . '_' . $scmFile->getFilename() . '.csv';

        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=iso-8859-1');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $filename));

        return $response;
    }
}