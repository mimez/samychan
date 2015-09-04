<?php

namespace MM\SamyEditorBundle\Scm\Packer;

use MM\SamyEditorBundle\Entity;
use Symfony\Component\Yaml;
use MM\SamyEditorBundle\Scm\Sqlite3Database;

class Sqlite3Packer extends AbstractPacker {

    /**
     * assamble FileData
     *
     * @param Entity\ScmFile $scmFile
     * @return string
     */
    protected function assambleFileData(Entity\ScmFile $scmFile)
    {
        $fileBinaryData = is_resource($scmFile->getData()) ? stream_get_contents($scmFile->getData()) : $scmFile->getData();

        // if we dont have any channels, return the original binary data
        if (count($scmFile->getChannels()) == 0) {
            return $fileBinaryData;
        }

        // load Config
        $fileConfig = $this->getConfiguration()->getConfigBySeries($scmFile->getScmPackage()->getSeries());
        $fileConfig = $fileConfig[$scmFile->getFilename()];

        // open sqlite-db
        $db = new Sqlite3Database($fileBinaryData);

        foreach ($scmFile->getChannels() as $scmChannel) {
            $this->updateChannelData($scmChannel, $fileConfig, $db->getSqlite3());
        }

        $db->disconnect();

        return $db->getBinary();
    }

    /**
     * Sqlite3 Data des Channels aktualisieren
     *
     * @param ScmChannel $scmChannel
     * @param array $fileConfig
     * @param \SQLite3 $sqlite3
     */
    protected function updateChannelData(Entity\ScmChannel $scmChannel, array $fileConfig, \SQLite3 $sqlite3)
    {
        // iterate over fields and write the new value of each into the binaryString
        $values = [];
        foreach ($fileConfig['fields'] as $fieldName => $fieldConfig) {

            // update only editable fields
            if (!isset($fieldConfig['saveable']) || $fieldConfig['saveable'] == false) {
                continue;
            }

            // custom save-handler? skip this field...
            if (isset($fieldConfig['savehandler'])) {
                $saveHandler = new $fieldConfig['savehandler']();
                $saveHandler->save($fieldName, $fieldConfig, $scmChannel, $sqlite3);
                continue;
            }

            // get value from entity
            $fieldValue = $scmChannel->{'get' . ucfirst($fieldName)}();

            // load datatype and cast the value to binary
            $dataType = new $fieldConfig['type'];
            $binaryFieldValue = $dataType->toBinary($fieldValue);

            $values[':' . $fieldName] = $binaryFieldValue;
        }

        // update channel
        $sth = $sqlite3->prepare($fileConfig['updateSqlQuery']);

        foreach ($values as $column => $value) {

            $sth->bindValue($column, $value, $column == ':name' ? SQLITE3_BLOB : SQLITE3_INTEGER); // @todo: type-handling
        }

        $sth->execute();
    }
}