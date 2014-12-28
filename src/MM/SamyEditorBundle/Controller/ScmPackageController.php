<?php

namespace MM\SamyEditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MM\SamyEditorBundle\Scm;
use MM\SamyEditorBundle\Entity;

class ScmPackageController extends Controller
{
    public function uploadAction(Request $request)
    {
        $defaultData = array();
        $form = $this->createFormBuilder($defaultData, array('csrf_protection' => false))
            ->add('file', 'file')
            ->add('send', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            try {
                // data is an array with "name", "email", and "message" keys
                $data = $form->getData();
                $em = $this->get('doctrine')->getManager();
                $scmArchiveLoader = $this->get('mm_samy_editor.scm_parser');
                $scmPackage = $scmArchiveLoader->load($data['file']->openFile());
                $scmPackage->setFilename($data['file']->getClientOriginalName());
                $em->persist($scmPackage);
                $em->flush();

                return $this->redirectToRoute('mm_samy_editor_scm_package', array('hash' => $scmPackage->getHash()));
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

        // pack the scm package
        $scmBinaryData = $this->get('mm_samy_editor.scm_packer')->pack($scmPackage);

        header('Content-Type: application/zip');
        header(sprintf('Content-Length: %s', strlen($scmBinaryData)));
        header(sprintf('Content-Disposition: attachment; filename="%s"', $scmPackage->getFilename()));
        echo $scmBinaryData;
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
            $em->getConnection()->beginTransaction();

            // read old channelNo (a bit stupid, old value is not reachable via entity
            $q = "SELECT c.channelNo FROM MM\SamyEditorBundle\Entity\ScmChannel c WHERE c.scm_channel_id = :scmChannelId";
            $oldChannelNo = $this->get('doctrine')->getManager()->createQuery($q)
                ->setParameter('scmChannelId', $scmChannel->getScmChannelId())
                ->getSingleScalarResult();

            // save the channel
            $data = $form->getData();
            $data->setUpdatedAt(new \DateTime());
            $em->persist($data);
            $em->flush();

            // reorder channels if necessary
            $scmOrderer = $this->get('mm_samy_editor.scm_orderer');
            $scmOrderer->reorderChannels(
                $scmChannel->getScmFile(),
                ($oldChannelNo - $data->getChannelNo()) > 0 ? 'bottomup' : 'topdown'
            );

            $em->getConnection()->commit();

            $request->getSession()->getFlashBag()->add(
                'success',
                'Channel successfully saved'
            );

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


    /**
     * Embedded Controller um die Sidebar zu bauen
     *
     * @param Entity\ScmPackage $scmPackage
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sidebarAction(Entity\ScmPackage $scmPackage)
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

        // Generate Navitems
        $navitems = array();
        foreach ($scmPackage->getFiles() as $scmFile) {

            // if the scmFile is not supported, we dont display it in the sidebar
            if (!isset($supportedFiles[$scmFile->getFilename()])) {
                continue;
            }

            // supported scmFile, go on and generate the nav item
            $navitem = $supportedFiles[$scmFile->getFilename()];
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
}