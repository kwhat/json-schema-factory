<?php

namespace JsonSchema\Template\Swagger\v2_0\Schema;

use JsonSchema\AbstractSchema;
use stdClass;

class Response extends AbstractSchema
{
    /**
     * @required
     * @var string $description
     */
    public $description;

    /**
     * @var string|stdClass $schema
     */
    public $schema;

    /**
     * @var string $termsOfService
     */
    public $headers;

    /**
     * @var stdClass $examples
     */
    public $example;
}
