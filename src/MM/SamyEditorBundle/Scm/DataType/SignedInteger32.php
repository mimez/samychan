<?php

namespace MM\SamyEditorBundle\Scm\DataType;

class SignedInteger32 implements DataTypeInterface {

    public function fromBinary($data)
    {
        $unpackedData = unpack('l1value', $data);

        return $unpackedData['value'];
    }

    public function toBinary($data, $length = null)
    {
        return pack('l1', $data);
    }
}