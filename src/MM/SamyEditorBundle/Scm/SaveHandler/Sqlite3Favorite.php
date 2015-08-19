<?php
namespace MM\SamyEditorBundle\Scm\SaveHandler;

use MM\SamyEditorBundle\Entity;

class Sqlite3Favorite implements SaveHandlerInterface
{

    public function save($fieldname, array $fieldConfig, Entity\ScmChannel $scmChannel, \PDO $pdo)
    {
        // we need the favno, if we dont got it, there is something wrong
        if (!isset($fieldConfig['additional_data']['favno'])) {
            throw new \Exception ('missing favno');
        }

        $favNo = $fieldConfig['additional_data']['favno'];

        // if the channel has no fav-sort, it isnt a favorite (or not any longer). make sure that the channel is deleted
        if (!strlen($scmChannel->{'getFav' . $favNo . 'sort'}())) {
            $pdo->prepare("DELETE FROM srv_fav WHERE srvId = :srvId AND fav = :fav")->execute(array(
                ':srvId' => $scmChannel->getIdentifier(),
                ':fav' => $favNo
            ));

            return;
        }

        // channel is a favorite channel. check if there is already a db-row
        $sth = $pdo->prepare("SELECT sfavId FROM srv_fav WHERE srvId = :srvId AND fav = :fav");
        $sth->execute(array(
            ':srvId' => $scmChannel->getIdentifier(),
            ':fav' => $favNo
        ));
        $sfavId = $sth->fetchColumn();

        if ($sfavId) {
            $pdo->prepare("UPDATE srv_fav SET pos = :pos WHERE srvId = :srvId AND fav = :fav")->execute(array(
                ':pos' => $scmChannel->{'getFav' . $favNo . 'sort'}(),
                ':srvId' => $scmChannel->getIdentifier(),
                ':fav' => $favNo
            ));
        } else {
            $pdo->prepare("INSERT INTO srv_fav (srvId, fav, pos) VALUES (:srvId, :fav, :pos)")->execute(array(
                ':pos' => $scmChannel->{'getFav' . $favNo . 'sort'}(),
                ':srvId' => $scmChannel->getIdentifier(),
                ':fav' => $favNo
            ));
        }
    }
}