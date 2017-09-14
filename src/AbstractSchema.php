<?php

namespace JsonSchema;

use stdClass;

abstract class AbstractSchema implements SchemaInterface
{
    /**
     * @uniqueItems
     * @minItems 1
     * @var string[] $enum
     */
    public $enum;

    /**
     * @required
     * @uniqueItems
     * @minItems 1
     * @enum array boolean integer number null object string
     * @var string|string[] $type
     */
    public $type;

    /**
     * @minItems 1
     * @var AbstractSchema[] $allOf
     */
    public $allOf;

    /**
     * @minItems 1
     * @var AbstractSchema[] $anyOf
     */
    public $anyOf;

    /**
     * @minItems 1
     * @var AbstractSchema[] $oneOf
     */
    public $oneOf;

    /**
     * @minItems 1
     * @var SchemaInterface $not
     */
    public $not;

    /**
     * @generic SchemaInterface
     * @var stdClass $definitions
     */
    public $definitions;

    /**
     * @var string $title
     */
    public $title;

    /**
     * @var string $description
     */
    public $description;

    /**
     * @var mixed $default
     */
    public $default;

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        // Trick to only return public properties from this scope.
        $schema = call_user_func("get_object_vars", $this);
        $schema["type"] = static::TYPE;

        return $schema;
    }

    /**
     * @inheritdoc
     */
    public static function schemaSerialize()
    {
        return Factory::create(static::class);
    }
}
