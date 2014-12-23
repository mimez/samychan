<?php

namespace MM\SamyEditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MM\SamyEditorBundle\Scm;

class ScmPackageController extends Controller
{
    public function indexAction($hash)
    {
        $em = $this->get('doctrine');
        $scmPackage = $em->getRepository('MM\SamyEditorBundle\Entity\ScmPackage')->findOneBy(array('hash' => $hash));

        foreach ($scmPackage->getFiles() as $file)
        {
            echo $file->getFileName() . '<br>';

            echo "channels: <br>";
            foreach ($file->getChannels() as $channel)
            {
                echo $channel->getChannelNo() . ':' . $channel->getName() . '<br>';
            }

        }

        die();
    }

    public function fileAction($hash, $scmFileId)
    {
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

        return $this->render('MMSamyEditorBundle:ScmPackage:file.html.twig', array(
            'scmPackage' => $scmPackage,
            'scmChannels' => $scmChannels,
        ));
    }

    public function downloadAction($hash)
    {
        $em = $this->get('doctrine');
        $scmPackage = $em->getRepository('MM\SamyEditorBundle\Entity\ScmPackage')->findOneBy(array('hash' => $hash));

        $scmPacker = new Scm\Packer($scmPackage);
        $scm = $scmPacker->getScm();

        header('Content-Type: application/zip');
        header(sprintf('Content-Length: %s', strlen($scm)));
        header(sprintf('Content-Disposition: attachment; filename="%s"', $scmPackage->getFilename()));
        echo $scm;
        die();
    }

    public function channelAction($hash, $scmChannelId, Request $request)
    {


        $em = $this->get('doctrine')->getManager();

        $scmPackage = $em->getRepository('MM\SamyEditorBundle\Entity\ScmPackage')->findOneBy(array('hash' => $hash));
        $scmChannel = $em->getRepository('MM\SamyEditorBundle\Entity\ScmChannel')->find($scmChannelId);

        $form = $this->createFormBuilder($scmChannel)
            ->add('channelNo', 'integer')
            ->add('name', 'text')
            ->add('save', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $em->persist($data);
            $em->flush();

            return $this->redirectToRoute('mm_samy_editor_scm_channel', array(
                'hash' => $scmPackage->getHash(),
                'scmChannelId' => $scmChannel->getScmChannelId()
            ));
        }

        return $this->render('MMSamyEditorBundle:ScmPackage:channel.html.twig', array(
            'scmChannel' => $scmChannel,
            'scmPackage' => $scmPackage,
            'form' => $form->createView(),
        ));

        /*
        $scmUitlities = $this->get('mm_samy_editor.scm_utilities');
        $scmUitlities->updateChannelData($scmChannel);

        echo bin2hex(stream_get_contents($scmChannel->getData()));
        die;
        echo hexdec(bin2hex($foo[0]));
        var_dump(bin2hex($foo));die('foo');

        echo pack('S1@64A200', $scmChannel->getChannelNo(), $scmChannel->getName());
        echo($scmChannel->getChannelNo());
        die('foo');*/
    }


}