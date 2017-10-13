<?php

namespace JsonSchema\Template\OpenAPI\v2_0\Security\Flow;

use JsonSchema\AbstractSchema;

class Implicit extends AbstractSchema
{
    /**
     * @required
     * @var string $authorizationUrl
     */
    public $authorizationUrl;
}
