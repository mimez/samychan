<?php

namespace MM\SamyEditorBundle\Scm\DataType;

class String implements DataTypeInterface {

    public function fromBinary($data)
    {
        return trim(mb_convert_encoding($data, 'utf-8', 'utf-16'));
    }

    public function toBinary($data, $length = null)
    {
        $value = mb_convert_encoding($data, 'utf-16', 'utf-8');

        // fill up with 0x00
        if (isset($length)) {

            $nullByteFillLength = $length - strlen($value);
            $value = pack("H*a{$nullByteFillLength}", bin2hex($value), null);
        }

        return $value;
    }
}