<?php

namespace MM\SamyChan\BackendBundle\Scm\DataType;

interface DataTypeInterface {

    public function toBinary($data, $length = null);

    public function fromBinary($data);
}