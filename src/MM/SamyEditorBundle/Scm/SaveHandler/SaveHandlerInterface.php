<?php
namespace MM\SamyEditorBundle\Scm\SaveHandler;

use MM\SamyEditorBundle\Entity;

interface SaveHandlerInterface
{
    public function save($fieldname, array $fieldconfig, Entity\ScmChannel $scmChannel, \SQLite3 $sqlite3);
}