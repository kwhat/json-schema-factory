<?php

namespace JsonSchema\OpenAPI\v2_0\Schema;

use JsonSchema\AbstractSchema;

class Response extends AbstractSchema
{
    /**
     * @required
     * @var string description
     */
    public $description;

    /**
     * @var string $description
     */
    public $schema;

    /**
     * @var string $termsOfService
     */
    public $headers;

    /**
     * @var Info\Contact $contact
     */
    public $examples;
}
