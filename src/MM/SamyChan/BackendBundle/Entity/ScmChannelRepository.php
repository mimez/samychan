<?php
namespace MM\SamyChan\BackendBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ScmChannelRepository extends EntityRepository
{
    /**
     * @param ScmFile $scmFile
     *
     * @return array
     */
    public function findChannelsByScmFile(ScmFile $scmFile, $order = null)
    {
        $q = "SELECT
                c
              FROM
                MM\SamyChan\BackendBundle\Entity\ScmChannel c
              WHERE
                c.scmFile = :scmFile AND
                c.channelNo > 0
              ORDER BY
                " . (isset($order) ? 'c.' . $order . ',' : '') . "
                c.channelNo";
        $dq = $this->getEntityManager()->createQuery($q);
        $dq->setParameters(array('scmFile' => $scmFile));

        return $dq->getResult();
    }

    /**
     * @param ScmPackage $scmPackage
     *
     * @return array
     */
    public function findChannelsByScmPackage(ScmPackage $scmPackage, $order = null)
    {
        $q = "SELECT
                c
              FROM
                MM\SamyChan\BackendBundle\Entity\ScmChannel c
                JOIN c.scmFile f
              WHERE
                f.scmPackage = :scmPackage AND
                c.channelNo > 0
              ORDER BY
                " . (isset($order) ? 'c.' . $order . ',' : '') . "
                c.channelNo";
        $dq = $this->getEntityManager()->createQuery($q);
        $dq->setParameters(array('scmPackage' => $scmPackage));

        return $dq->getResult();
    }
}