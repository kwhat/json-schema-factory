<?php

namespace JsonSchema\Template\Swagger\v2_0\Schema;

use JsonSchema\AbstractSchema;
use JsonSchema\SchemaInterface;
use stdClass;

class Response extends AbstractSchema
{
    /**
     * @required
     * @var string $description
     */
    public $description;

    /**
     * @var SchemaInterface $schema
     */
    public $schema;

    /**
     * @patternProperties [a-zA-Z0-9\-]+ Header
     * @var stdClass $headers
     */
    public $headers;

    /**
     * @patternProperties [a-zA-Z\-]+/[a-zA-Z\-]+
     * @var SchemaInterface $examples
     */
    public $examples;
}
