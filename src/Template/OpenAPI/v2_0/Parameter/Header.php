<?php

namespace JsonSchema\Template\v2_0\OpenAPI\Parameter;

use JsonSchema\AbstractSchema;

class Header extends AbstractSchema
{
    /**
     * @required
     * @var string $name
     */
    public $name;

    /**
     * @required
     * @enum header
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
