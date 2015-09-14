<?php
namespace MM\SamyChan\BackendBundle\Scm\SaveHandler;

use MM\SamyChan\BackendBundle\Entity;

interface SaveHandlerInterface
{
    public function save($fieldname, array $fieldconfig, Entity\ScmChannel $scmChannel, \SQLite3 $sqlite3);
}