<?php

namespace JsonSchema;

abstract class AbstractSchema implements SchemaInterface
{
    /**
     * @inheritdoc
     */
    public function jsonSerialize() {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function schemaSerialize()
    {
        return Factory::create(static::class);
    }
}
