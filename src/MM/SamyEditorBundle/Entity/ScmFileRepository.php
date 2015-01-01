<?php
namespace MM\SamyEditorBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ScmFileRepository extends EntityRepository
{
    /**
     * find a scmFile and validates the hash of the scmPackage
     * @param integer $scmFileId
     * @param string $scmPackageHash
     *
     * @return scmFile
     * @throws \Exception
     */
    public function findAndValidateHash($scmFileId, $scmPackageHash)
    {
        $scmFile = $this->find($scmFileId);

        // scmfile found?
        if (!isset($scmFile)) {
            throw new \Exception(sprintf('scmFile=(%s) not found', $scmFileId));
        }

        // check hash
        if ($scmFile->getScmPackage()->getHash() != $scmPackageHash) {
            throw new \Exception('access not allowed');
        }

        return $scmFile;
    }
}