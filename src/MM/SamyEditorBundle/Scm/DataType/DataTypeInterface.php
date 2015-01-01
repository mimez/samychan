<?php

namespace MM\SamyEditorBundle\Scm\DataType;

interface DataTypeInterface {

    public function toBinary($data, $length = null);

    public function fromBinary($data);
}