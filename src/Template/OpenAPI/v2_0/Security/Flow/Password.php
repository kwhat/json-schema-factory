<?php

namespace JsonSchema\Template\OpenAPI\v2_0\Security\Flow;

use JsonSchema\AbstractSchema;

class Password extends AbstractSchema
{
    /**
     * @required
     * @var string $authorizationUrl
     */
    public $tokenUrl;
}
