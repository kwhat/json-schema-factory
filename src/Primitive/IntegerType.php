<?php

namespace JsonSchema\Primitive;

use JsonSchema\AbstractSchema;
use JsonSchema\Exception;

class IntegerType extends NumberType
{
    /**
     * @enum int32 int64
     * @var string $format
     */
    public $format;
}
