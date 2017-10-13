<?php

namespace JsonSchema\Template\OpenAPI\v2_0\Schema;

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
