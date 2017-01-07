<?php

namespace Bildr\Json\Schema;

class NullType implements GenericType
{
    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array("type" => "null");
    }
}
