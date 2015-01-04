<?php

namespace MM\SamyEditorBundle\Scm\DataType;

class Boolean implements DataTypeInterface {

    public function fromBinary($data)
    {
        return hexdec(bin2hex($data)) > 0;
    }

    public function toBinary($data, $length = null)
    {
        return hex2bin($data); // @todo
    }
}