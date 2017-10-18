<?php

namespace JsonSchema\Template\Swagger\v2_0\Schema\Info;

use JsonSchema\AbstractSchema;

class Contact extends AbstractSchema
{
    /**
     * @required
     * @var string $name
     */
    public $name;

    /**
     * @var string $url
     */
    public $url;

    /**
     * @var string $email
     */
    public $email;
}
