<?php

namespace MM\SamyChan\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MM\SamyChan\BackendBundle\Scm;
use MM\SamyChan\BackendBundle\Entity;

class ScmFavoriteController extends Controller
{
    public function indexAction($hash, $favNo, Request $request)
    {
        $em = $this->get('doctrine')->getManager();

        // load scmPackage
        $scmPackage = $em->getRepository('MM\SamyChan\BackendBundle\Entity\ScmPackage')->findOneBy(array('hash' => $hash));

        // get all channels of this file
        $scmChannels = $em->getRepository('MM\SamyChan\BackendBundle\Entity\ScmChannel')->findChannelsByScmPackage($scmPackage, 'fav' . $favNo . 'sort');

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
            foreach ($scmPackage->getFiles() as $scmFile) {
                $q = "UPDATE MM\SamyChan\BackendBundle\Entity\ScmChannel c SET {$field} = -1 WHERE {$field} > 0 AND c.scmFile = :scmFile";
                $em->createQuery($q)->setParameter('scmFile', $scmFile)->getResult();
                $em->clear();
            }

            // update channels with new favorit sort
            $sort = 1;
            $data = $request->request->all();
            foreach ($data['form']['channels'] as $scmChannelId) {
                $scmChannel = $em->getRepository('MM\SamyChan\BackendBundle\Entity\ScmChannel')->find($scmChannelId); // load channel
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

            return $this->redirectToRoute('mm_samy_editor_scm_favorites', array(
                'hash' => $scmFile->getScmPackage()->getHash(),
                'favNo' => $favNo
            ));
        }

        return $this->render('MMSamyChanBackendBundle:ScmFavorite:index.html.twig', array(
            'form' => $form->createView(),
            'scmPackage' => $scmPackage,
            'scmChannels' => $scmChannels,
            'favNo' => $favNo,
        ));
    }
}