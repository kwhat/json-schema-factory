<?php

namespace JsonSchema\Template\v2_0\OpenAPI\Parameter;

use JsonSchema\AbstractSchema;

class FormData extends AbstractSchema
{
    /**
     * @required
     * @var string $name
     */
    public $name;

    /**
     * @required
     * @enum formData
     * @var string $in
     */
    public $in;

    /**
     * @var string $description
     */
    public $description;

    /**
     * @var bool $required
     */
    public $required;

}
