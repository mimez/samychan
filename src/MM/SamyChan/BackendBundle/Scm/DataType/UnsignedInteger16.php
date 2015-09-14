<?php

namespace MM\SamyChan\BackendBundle\Scm\DataType;

class UnsignedInteger16 implements DataTypeInterface {

    public function fromBinary($data)
    {
        $unpackedData = unpack('S1value', $data);

        return $unpackedData['value'];
    }

    public function toBinary($data, $length = null)
    {
        return pack('S1', $data);
    }
}