<?php

namespace JsonSchema\Condition;

use JsonSchema\SchemaInterface;

class AllOf implements SchemaInterface
{
    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $schema = array(
            "type" => "allOf"
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
