<?php
namespace MM\SamyEditorBundle\Scm\DataType;

class Plain implements DataTypeInterface {

    public function fromBinary($data)
    {
        return $data;
    }

    public function toBinary($data, $length = null)
    {
        return $data;
    }
}