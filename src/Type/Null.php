<?php

namespace Json\Schema;

class NullType implements TypeInterface
{
    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array("type" => "null");
    }
}
