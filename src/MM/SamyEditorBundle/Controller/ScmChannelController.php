<?php

namespace MM\SamyEditorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MM\SamyEditorBundle\Scm;
use MM\SamyEditorBundle\Entity;

class ScmChannelController extends Controller
{
    public function channelAction($hash, $scmChannelId, Request $request)
    {
        $em = $this->get('doctrine')->getManager();

        $scmPackage = $em->getRepository('MM\SamyEditorBundle\Entity\ScmPackage')->findOneBy(array('hash' => $hash));
        $scmChannel = $em->getRepository('MM\SamyEditorBundle\Entity\ScmChannel')->find($scmChannelId);

        $form = $this->createFormBuilder($scmChannel, array('csrf_protection' => false))
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
            /*$scmOrderer = $this->get('mm_samy_editor.scm_orderer');
            $scmOrderer->reorderChannels(
                $scmChannel->getScmFile(),
                ($oldChannelNo - $data->getChannelNo()) > 0 ? 'bottomup' : 'topdown'
            );*/

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
    }
}