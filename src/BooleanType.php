<?php

namespace Bildr\Json\Schema;

class BooleanType implements GenericType
{
    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array("type" => "boolean");
    }
}
