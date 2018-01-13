<?php

namespace JsonSchema\Template\Swagger\v2_0\Schema;

use JsonSchema\AbstractSchema;
use JsonSchema\SchemaInterface;

class Parameter extends AbstractSchema
{
    /**
     * @required
     * @var string $name
     */
    public $name;

    /**
     * @required
     * @enum body|formData|header|path|query
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
