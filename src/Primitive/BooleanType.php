<?php

namespace JsonSchema\Primitive;

use JsonSchema\SchemaInterface;

class BooleanType implements SchemaInterface
{
    const TYPE = "boolean";

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $schema = array(
            "type" => static::TYPE
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
