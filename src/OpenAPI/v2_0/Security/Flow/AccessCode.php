<?php

namespace JsonSchema\OpenAPI\v2_0\Security\Flow;

use JsonSchema\AbstractSchema;

class AccessCode extends AbstractSchema
{
    /**
     * @required
     * @var string $authorizationUrl
     */
    public $tokenUrl;
}
