<?php

namespace JsonSchema;

use JsonSchema\Primitive\BooleanType;
use JsonSchema\Primitive\IntegerType;
use JsonSchema\Primitive\NullType;
use JsonSchema\Primitive\NumberType;
use JsonSchema\Primitive\StringType;
use stdClass;

abstract class AbstractSchema implements SchemaInterface
{
    /**
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

    public $allOf;

    public $anyOf;

    public $oneOf;

    public $not;

    /**
     * @generic BooleanType | IntegerType | NullType | NumberType | StringType
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
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        // Little trick to only return public properties from this scope.
        return call_user_func("get_object_vars", $this);
    }

    /**
     * @inheritdoc
     */
    public static function schemaSerialize()
    {
        return Factory::create(static::class);
    }
}
