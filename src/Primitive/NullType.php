<?php

namespace JsonSchema\Primitive;

use JsonSchema\SchemaInterface;

class NullType implements SchemaInterface
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

    /**
     * Produces a json serializable schema to represent this class.
     *
     * @return SchemaInterface
     */
    public static function schemaSerialize()
    {
        // TODO: Implement schemaSerialize() method.
    }
}
