<?php

namespace MM\SamyEditorBundle\Scm\DataType;

class ServiceType implements DataTypeInterface {

    public function fromBinary($data)
    {
        return bin2hex($data);
    }

    public function toBinary($data, $length = null)
    {
        return hex2bin($data);
    }
}