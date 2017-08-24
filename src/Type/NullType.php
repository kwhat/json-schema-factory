<?php

namespace Json\Schema;

class NullType implements TypeInterface
{
    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $schema = array("type" => "null");
        return $schema;
    }
}
