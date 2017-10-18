<?php

namespace JsonSchema\Template\Swagger\v2_0\Schema\Path\Item;

use JsonSchema\Template\Swagger\v2_0\Parameter;
use JsonSchema\Template\Swagger\v2_0\Schema;
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
     * @var Schema\ExternalDocumentation $externalDocs
     */
    public $externalDocs;

    /**
     * @var string $operationId
     */
    public $operationId;

    /**
     * @uniqueItems
     * @minItems 1
     * @pattern [a-zA-Z\-]+/[a-zA-Z\-]+
     * @var string[] $consumes
     */
    public $consumes;

    /**
     * @uniqueItems
     * @minItems 1
     * @pattern ^[a-zA-Z0-9\-]+\/[a-zA-Z0-9\-]+
     * @var string[] $produces
     */
    public $produces;
    
    /**
     * @patternProperties .+ Parameter\Body|Parameter\FormData|Parameter\Header|Parameter\Path|Parameter\Query
     * @var stdClass $parameters
     */
    public $parameters;

    /**
     * @patternProperties .+ Security\ApiKey|Security\Basic|Security\OAuth2
     * @var stdClass $security
     */
    public $security;
}
