<?php

namespace JsonSchema\Template\Swagger\v2_0\Security\Flow;

use JsonSchema\AbstractSchema;

class Implicit extends AbstractSchema
{
    /**
     * @required
     * @var string $authorizationUrl
     */
    public $authorizationUrl;
}
