<?php

namespace JsonSchema\Condition;

use JsonSchema\SchemaInterface;

class OneOf implements SchemaInterface
{
    const TYPE = "oneOf";

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $schema = array(
            static::TYPE => null
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
