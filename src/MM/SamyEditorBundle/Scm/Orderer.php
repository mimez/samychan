<?php

namespace MM\SamyEditorBundle\Scm;

use MM\SamyEditorBundle\Entity;

/**
 * Service for reordering channel-lists
 *
 * @package MM\SamyEditorBundle\Scm
 */
class Orderer {

    /**
     * @var \Symfony\Bridge\Doctrine\RegistryInterface
     */
    protected $doctrine;

    /**
     * @var array $sortconfig
     */
    protected $sortconfig = array(
        'bottomup' => array(
            'lastChannelNo' => 0,
            'sqlSortDir' => 'ASC',
            'moveOffset' => 1,
            'crementorFunction' => 'max',
            'conflictOperator' => '<='
        ),

        'topdown' => array(
            'lastChannelNo' => '99999',  // if we should sort top down, we start with the last channel
            'sqlSortDir' => 'DESC',
            'moveOffset' => -1,
            'crementorFunction' => 'min',
            'conflictOperator' => '>='
        ),
    );

    /**
     * @return \Symfony\Bridge\Doctrine\RegistryInterface
     */
    public function getDoctrine()
    {
        return $this->doctrine;
    }

    /**
     * @param \Symfony\Bridge\Doctrine\RegistryInterface $doctrine
     */
    public function setDoctrine($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @param \Symfony\Bridge\Doctrine\RegistryInterface $doctrine
     */
    public function __construct(\Symfony\Bridge\Doctrine\RegistryInterface $doctrine)
    {
        $this->setDoctrine($doctrine);
    }


    /**
     * Reorder channels
     *
     * @param Entity\ScmFile $scmFile
     * @param string $sortdir
     * @throws \Exception
     */
    public function reorderChannels(Entity\ScmFile $scmFile, $sortdir = 'bottomup')
    {
        $em = $this->getDoctrine()->getManager();

        // load sort config
        $sortconfig = $this->getSortConfigByDirection($sortdir, $scmFile);

        // get channels by direction
        $scmChannels = $this->getChannels($scmFile, $sortconfig['sqlSortDir']);

        $lastChannelNo = $sortconfig['lastChannelNo'];
        foreach ($scmChannels as $scmChannel) {

            // fetch scmChannel from doctrine
            $scmChannel = $em->getRepository('MM\SamyEditorBundle\Entity\ScmChannel')->find($scmChannel['scm_channel_id']);

            if ($scmChannel->getChannelNo() == 0) {
                continue;
            }

            // CONFLICT: current channel has a number that is already assigned
            if (version_compare($scmChannel->getChannelNo(), $lastChannelNo, $sortconfig['conflictOperator']) > 0) {
                $scmChannel->setChannelNo($lastChannelNo + $sortconfig['moveOffset']);
                $em->persist($scmChannel);
            }

            // setting lastChannelNo to current channel or lastChannelNo
            $lastChannelNo = call_user_func($sortconfig['crementorFunction'], $scmChannel->getChannelNo(), $lastChannelNo);
        }

        $em->flush();
    }

    /**
     * Reordering channels (gapless)
     *
     * @param Entity\ScmFile $scmFile
     */
    public function reorderChannelsGapless(Entity\ScmFile $scmFile)
    {
        $em = $this->getDoctrine()->getManager();

        // get channels by direction
        $scmChannels = $this->getChannels($scmFile);

        $currentChannelNo = 1;
        foreach ($scmChannels as $scmChannel) {

            // fetch scmChannel from doctrine
            $scmChannel = $em->getRepository('MM\SamyEditorBundle\Entity\ScmChannel')->find($scmChannel['scm_channel_id']);

            if ($scmChannel->getChannelNo() == 0) {
                continue;
            }

            // CONFLICT
            if ($scmChannel->getChannelNo() != $currentChannelNo) {
                $scmChannel->setChannelNo($currentChannelNo);
                $em->persist($scmChannel);
            }

            // setting lastChannelNo to current channel or lastChannelNo
            $currentChannelNo++;
        }

        $em->flush();
    }

    /**
     * Helper for fetching Channels from the DB
     *
     * @param Entity\ScmFile $scmFile
     * @param string $sortDir
     * @return array
     */
    protected function getChannels(Entity\ScmFile $scmFile, $sortDir = 'ASC')
    {
        $em = $this->getDoctrine()->getManager();

        // get channels
        // we do this by a native sql because we need to order by updatedAt in a special way (NULLS last).
        // we cannot do that with dql / doctrine :(
        $q = "SELECT
                c.scm_channel_id
              FROM
                scm_channels c
              WHERE
                c.scm_file_id = :scmFileId
              ORDER BY
                c.channelNo {$sortDir},
                c.updatedAt IS NOT NULL DESC,
                c.updatedAt DESC";
        $scmChannels = $em->getConnection()->executeQuery($q, array('scmFileId' => $scmFile->getScmFileId()))->fetchAll();

        return $scmChannels;
    }

    /**
     * Max ChannelNo ermitteln
     *
     * @param Entity\ScmFile $scmFile
     * @return integer
     */
    public function getMaxChannelNo(Entity\ScmFile $scmFile)
    {
        $q = "SELECT
                MAX(c.channelNo) AS channelNo
              FROM
                MM\SamyEditorBundle\Entity\ScmChannel c
              WHERE
                c.scmFile = :scmFile";

        return $this->getDoctrine()->getManager()->createQuery($q)
            ->setParameter('scmFile', $scmFile)
            ->getSingleScalarResult();
    }

    /**
     * @param $sortdir
     *
     * @return array $sortconfig
     *
     * @throws \Exception
     */
    public function getSortConfigByDirection($sortdir, Entity\ScmFile $scmFile) {
        if (!isset($this->sortconfig[$sortdir])) {
            throw new \Exception(sprintf('sortdir=(%s) not implemented', $sortdir));
        }

        $config = $this->sortconfig[$sortdir];

        if ('topdown' == $sortdir) {
            $config['lastChannelNo'] = $this->getMaxChannelNo($scmFile) + 1;
        }

        return $config;
    }
}