<?php

namespace JsonSchema\v2_0\OpenAPI\Parameter;

use JsonSchema\AbstractSchema;

class Query extends AbstractSchema
{
    /**
     * @required
     * @var string $name
     */
    public $name;

    /**
     * @required
     * @enum query
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
}
