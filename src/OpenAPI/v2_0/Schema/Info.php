<?php

namespace JsonSchema\OpenAPI\v2_0\Schema;

use JsonSchema\AbstractSchema;

class Info extends AbstractSchema
{
    /**
     * @required
     * @maxLength 255
     * @var string $title
     */
    public $title;

    /**
     * @var string $description
     */
    public $description;

    /**
     * @var string $termsOfService
     */
    public $termsOfService;

    /**
     * @var Info\Contact $contact
     */
    public $contact;

    /**
     * @var Info\License $license
     */
    public $license;

    /**
     * @required
     * @pattern ^v[0-9].*
     * @var string $version
     */
    public $version;
}
