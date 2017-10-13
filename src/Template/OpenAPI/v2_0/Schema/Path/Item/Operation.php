<?php

namespace JsonSchema\Template\v2_0\OpenAPI\Path\Item;

use Bildr\API;
use Bildr\API\Json\v2_0\OpenAPI;
use JsonSchema\AbstractSchema;
use stdClass;

class Operation extends AbstractSchema
{
    /**
     * @var string[] $tags
     */
    public $tags;

    /**
     * @maxLength 120
     * @var string $summary
     */
    public $summary;

    /**
     * @var string $description
     */
    public $description;

    /**
     * @var OpenAPI\ExternalDocumentation $externalDocs
     */
    public $externalDocs;

    /**
     * @var string $operationId
     */
    public $operationId;

    /**
     * @var string[] $consumes
     */
    public $consumes;

    /**
     * @var string[] $produces
     */
    public $produces;

    /**
     * @generic OpenAPI\AbstractParameter
     * @var stdClass $parameters
     */
    public $parameters;

    /**
     * @var string[] $parameters
     */
    public $security;
}
