<?php

namespace JsonSchema\Template\Swagger\v2_0\Parameter;

use JsonSchema\AbstractSchema;

class Path extends AbstractSchema
{
    /**
     * @required
     * @var string $name
     */
    public $name;

    /**
     * @required
     * @enum path
     * @var string $in
     */
    public $in;

    /**
     * @var string $description
     */
    public $description;

    /**
     * @required
     * @enum true
     * @var bool $required
     */
    public $required;
}
