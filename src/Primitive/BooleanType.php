<?php

namespace JsonSchema\Primitive;

use JsonSchema\TypeInterface;

class BooleanType implements TypeInterface
{
    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $schema = array("type" => "boolean");
        return $schema;
    }
}
