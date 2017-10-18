<?php

namespace JsonSchema\Template\Swagger\v2_0\Security\Flow;

use JsonSchema\AbstractSchema;

class Application extends AbstractSchema
{
    /**
     * @required
     * @var string $authorizationUrl
     */
    public $tokenUrl;
}
