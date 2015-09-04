<?php
namespace MM\SamyEditorBundle\Scm\SaveHandler;

use MM\SamyEditorBundle\Entity;

class Sqlite3Favorite implements SaveHandlerInterface
{
    const UPDATE_QUERY = 'UPDATE srv_fav SET pos = :pos WHERE srvId = :srvId AND fav = :favNo';
    const INSERT_QUERY = 'INSERT INTO srv_fav (srvId, fav, pos) VALUES (:srvId, :favNo, :pos)';

    /**
     * @param $fieldname
     * @param array $fieldConfig
     * @param Entity\ScmChannel $scmChannel
     * @param \SQLite3 $sqlite3
     * @throws \Exception
     */
    public function save($fieldname, array $fieldConfig, Entity\ScmChannel $scmChannel, \SQLite3 $sqlite3)
    {
        // we need the favno, if we dont got it, there is something wrong
        if (!isset($fieldConfig['additional_data']['favno'])) {
            throw new \Exception ('missing favno');
        }

        $favNo = $fieldConfig['additional_data']['favno'];

        // if the channel has no fav-sort, it isnt a favorite (or not any longer). make sure that the channel is deleted
        if (!strlen($scmChannel->{'getFav' . $favNo . 'sort'}())) {
            $sth = $sqlite3->prepare("DELETE FROM srv_fav WHERE srvId = :srvId AND fav = :fav");
            $sth->bindValue(':srvId', $scmChannel->getIdentifier(), SQLITE3_INTEGER);
            $sth->bindValue(':fav', $favNo, SQLITE3_INTEGER);

            return;
        }

        if ($this->favRecordExists($scmChannel->getIdentifier(), $favNo, $sqlite3)) {
            $q = self::UPDATE_QUERY;
        } else {
            $q = self::INSERT_QUERY;
        }

        // perform database query
        $sth = $sqlite3->prepare($q);
        $sth->bindValue(':pos', $scmChannel->{'getFav' . $favNo . 'sort'}(), SQLITE3_INTEGER);
        $sth->bindValue(':srvId', $scmChannel->getIdentifier(), SQLITE3_INTEGER);
        $sth->bindValue(':favNo', $favNo, SQLITE3_INTEGER);
        $sth->execute();
    }

    /**
     * Checks if a specific fav-record already exists
     *
     * @param integer $srvId
     * @param integer $favNo
     * @param \SQLite3 $sqlite3
     * @return bool
     */
    protected function favRecordExists($srvId, $favNo, \SQLite3 $sqlite3)
    {
        $sth = $sqlite3->prepare("SELECT sfavId FROM srv_fav WHERE srvId = :srvId AND fav = :fav");
        $sth->bindValue(':srvId', $srvId, SQLITE3_INTEGER);
        $sth->bindValue(':fav', $favNo, SQLITE3_INTEGER);

        return (bool)$sth->execute()->fetchArray();
    }
}