<?php

namespace JsonSchema\Primitive;

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
