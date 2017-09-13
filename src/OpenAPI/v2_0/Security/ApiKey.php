<?php

namespace JsonSchema\OpenAPI\v2_0\Security;

use JsonSchema\AbstractSchema;

class ApiKey extends AbstractSchema
{
    /**
     * @required
     * @pattern [\w]+
     * @var string $name
     */
    public $name;

    /**
     * @required
     * @enum query header
     * @var string $in
     */
    public $in;
}
