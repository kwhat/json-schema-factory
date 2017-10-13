<?php

namespace JsonSchema\Condition;

use JsonSchema\SchemaInterface;

class OneOf implements SchemaInterface
{
    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $schema = array(
            "type" => "oneOf"
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
