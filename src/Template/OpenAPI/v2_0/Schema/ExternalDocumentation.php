<?php

namespace JsonSchema\Template\v2_0\OpenAPI\Schema;

use JsonSchema\AbstractSchema;

class ExternalDocumentation extends AbstractSchema
{
    /**
     * @maxLength 255
     * @var string $description
     */
    public $description;

    /**
     * @maxLength 255
     * @var string $url
     */
    public $url;
}
