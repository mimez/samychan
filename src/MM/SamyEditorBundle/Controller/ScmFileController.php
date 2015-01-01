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

        // load scmFile
        $scmFile = $em->getRepository('MM\SamyEditorBundle\Entity\ScmFile')->findAndValidateHash($scmFileId, $hash);

        // get filemetadata. if not found, its no valid file
        $fileMeta = $this->helperGetFileMetaByName($scmFile->getFilename());
        if (false === $fileMeta) {
            throw new \Exception(sprintf('invalid scm-file=(%s)', $scmFile->getFilename()));
        }

        // load channels
        $scmChannels = $em->getRepository('MM\SamyEditorBundle\Entity\ScmChannel')->findChannelsByScmFile($scmFile);

        return $this->render('MMSamyEditorBundle:ScmFile:file.html.twig', array(
            'scmPackage' => $scmFile->getScmPackage(),
            'scmFile' => $scmFile,
            'scmChannels' => $scmChannels,
            'scmFileMeta' => $fileMeta,
        ));
    }

    public function fileJsonAction($hash, $scmFileId) {
        $em = $this->get('doctrine');

        // load scmFile
        $scmFile = $em->getRepository('MM\SamyEditorBundle\Entity\ScmFile')->findAndValidateHash($scmFileId, $hash);

        // load channels
        $scmChannels = $em->getRepository('MM\SamyEditorBundle\Entity\ScmChannel')->findChannelsByScmFile($scmFile);

        $data = array();
        foreach ($scmChannels as $scmChannel) {
            $data[] = array(
                'channelId' => $scmChannel->getScmChannelId(),
                'channelNo' => $scmChannel->getChannelNo(),
                'name' => $scmChannel->getName(),
                'fav1sort' => $scmChannel->getFav1sort(),
                'fav2sort' => $scmChannel->getFav2sort(),
                'fav3sort' => $scmChannel->getFav3sort(),
                'fav4sort' => $scmChannel->getFav4sort(),
                'fav5sort' => $scmChannel->getFav5sort(),
                'options' => '',
            );
        }
        // json response
        $response = new Response(json_encode(array('data' => $data)));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function fileReorderAction($hash, $scmFileId) {
        $em = $this->get('doctrine');

        // load scmFile
        $scmFile = $em->getRepository('MM\SamyEditorBundle\Entity\ScmFile')->findAndValidateHash($scmFileId, $hash);

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

    public function favoritesAction($hash, $scmFileId, $favNo, Request $request)
    {
        $em = $this->get('doctrine')->getManager();

        // load scmFile
        $scmFile = $em->getRepository('MM\SamyEditorBundle\Entity\ScmFile')->findAndValidateHash($scmFileId, $hash);

        // get all channels of this file
        $scmChannels = $em->getRepository('MM\SamyEditorBundle\Entity\ScmChannel')->findChannelsByScmFile($scmFile, 'fav1sort');

        // build data for multiple select
        $selectData = array();
        $selectedItems = array();
        foreach ($scmChannels as $scmChannel) {
            $selectData[$scmChannel->getScmChannelId()] = $scmChannel->getName();

            if ($scmChannel->{'getFav' . $favNo . 'sort'}() > 0) {
                $selectedItems[] = $scmChannel->getScmChannelId();
            }
        }

        // build form
        $defaultData = array();
        $form = $this->createFormBuilder($defaultData)
            ->add('channels', 'choice', array('multiple' => true, 'choices' => $selectData, 'data' => $selectedItems))
            ->add('Save', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {

            // reset all channels of this file and fav-list
            $field = 'c.fav' . $favNo . 'sort';
            $q = "UPDATE MM\SamyEditorBundle\Entity\ScmChannel c SET {$field} = -1 WHERE {$field} > 0 AND c.scmFile = :scmFile";
            $em->createQuery($q)->setParameter('scmFile', $scmFile)->getResult();
            $em->clear();

            // update channels with new favorit sort
            $sort = 1;
            $data = $request->request->all();
            foreach ($data['form']['channels'] as $scmChannelId) {
                $scmChannel = $em->getRepository('MM\SamyEditorBundle\Entity\ScmChannel')->find($scmChannelId); // load channel
                echo $scmChannel->getName() . '<br>';
                $scmChannel->{'setFav' . $favNo . 'sort'}($sort); // set new sort
                $em->persist($scmChannel);
                $sort++;
            }

            $em->flush();

            // user feedback
            $request->getSession()->getFlashBag()->add(
                'success',
                'Favorites has been saved'
            );

            return $this->redirectToRoute('mm_samy_editor_scm_file_favorites', array(
                'hash' => $scmFile->getScmPackage()->getHash(),
                'scmFileId' => $scmFile->getScmFileId(),
                'favNo' => $favNo
            ));
        }



        return $this->render('MMSamyEditorBundle:ScmFile:favorites.html.twig', array(
            'form' => $form->createView(),
            'scmPackage' => $scmFile->getScmPackage(),
            'scmFile' => $scmFile,
            'scmChannels' => $scmChannels,
            'favNo' => $favNo,
        ));
    }
}