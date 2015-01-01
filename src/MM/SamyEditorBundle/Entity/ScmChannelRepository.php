<?php
namespace MM\SamyEditorBundle\Entity;

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
                MM\SamyEditorBundle\Entity\ScmChannel c
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
}