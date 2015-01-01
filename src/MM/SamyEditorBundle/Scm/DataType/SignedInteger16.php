<?php

namespace MM\SamyEditorBundle\Scm\DataType;

class SignedInteger16 implements DataTypeInterface {

    public function fromBinary($data)
    {
        $unpackedData = unpack('s1value', $data);

        return $unpackedData['value'];
    }

    public function toBinary($data, $length = null)
    {
        return pack('s1', $data);
    }
}