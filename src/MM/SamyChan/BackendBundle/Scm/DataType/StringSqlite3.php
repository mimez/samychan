<?php

namespace MM\SamyChan\BackendBundle\Scm\DataType;

class StringSqlite3 implements DataTypeInterface {

    public function fromBinary($data)
    {
        return trim(mb_convert_encoding($data, 'utf-8', 'utf-16'));
    }

    public function toBinary($data, $length = null)
    {
        return mb_convert_encoding($data, 'utf-16', 'utf-8');
    }
}