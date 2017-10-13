<?php

namespace JsonSchema\Template\v2_0\OpenAPI\Parameter;

use JsonSchema\AbstractSchema;
use JsonSchema\SchemaInterface;

class Body extends AbstractSchema
{
    /**
     * @required
     * @var string $name
     */
    public $name;

    /**
     * @required
     * @enum body
     * @var string $in
     */
    public $in;

    /**
     * @var string $description
     */
    public $description;

    /**
     * @var bool $required
     */
    public $required;

    /**
     * @required
     * @var SchemaInterface $schema
     */
    public $schema;
}
