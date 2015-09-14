<?php

namespace MM\SamyChan\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use MM\SamyChan\BackendBundle\Scm;
use MM\SamyChan\BackendBundle\Entity;
use Symfony\Component\HttpFoundation\Response;

class ScmChannelController extends Controller
{
    public function channelAction($hash, $scmChannelId, Request $request)
    {
        $em = $this->get('doctrine')->getManager();

        $scmPackage = $em->getRepository('MM\SamyChan\BackendBundle\Entity\ScmPackage')->findOneBy(array('hash' => $hash));
        $scmChannel = $em->getRepository('MM\SamyChan\BackendBundle\Entity\ScmChannel')->find($scmChannelId);

        $form = $this->createFormBuilder($scmChannel, array('csrf_protection' => false))
            ->add('channelNo', 'integer')
            ->add('name', 'text')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->getConnection()->beginTransaction();

            // save the channel
            $data = $form->getData();
            $data->setUpdatedAt(new \DateTime());
            $em->persist($data);
            $em->flush();

            $em->getConnection()->commit();

            // json response
            $response = new Response(json_encode(array('response' => 'success')));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }

        return $this->render('MMSamyChanBackendBundle:ScmPackage:channel.html.twig', array(
            'scmChannel' => $scmChannel,
            'scmPackage' => $scmPackage,
            'form' => $form->createView(),
        ));
    }
}