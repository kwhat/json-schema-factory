<?php

namespace Bildr\Json\Schema;

class BooleanType implements TypeInterface
{
    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return array("type" => "boolean");
    }
}
