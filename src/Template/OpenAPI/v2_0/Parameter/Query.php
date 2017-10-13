<?php

namespace JsonSchema\Template\OpenAPI\v2_0\Parameter;

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
