<?php

namespace JsonSchema\Template\OpenAPI\v2_0\Schema;

use JsonSchema\AbstractSchema;
use JsonSchema\Collection\ArrayList;
use JsonSchema\Factory;
use JsonSchema\Primitive\BooleanType;
use JsonSchema\Primitive\IntegerType;
use JsonSchema\Primitive\NumberType;
use JsonSchema\Primitive\StringType;

class Header extends AbstractSchema
{
    /**
     * @var string description
     */
    public $description;

    /**
     * @var string $description
     */
    public $schema;

    /**
     * @var string $termsOfService
     */
    public $headers;

    /**
     * @required
     * @var ArrayList | BooleanType | IntegerType | NumberType | StringType $type
     */
    public $type;

    /**
     * @inheritdoc
     */
    public function jsonSerialize() {
        $schema = parent::jsonSerialize();
        if ($this->type !== null) {
            $schema = array_merge($schema, $this->type->jsonSerialize());
        }

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
