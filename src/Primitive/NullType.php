<?php

namespace JsonSchema\Primitive;

use JsonSchema\TypeInterface;

class NullType implements TypeInterface
{
    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $schema = array(
            "type" => "null"
        );

        return $schema;
    }
}
