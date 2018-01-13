<?php

namespace JsonSchema\Template\Swagger\v2_0\Schema\Path\Item;

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
     * @var Schema\Parameter[] $parameters
     */
    public $parameters;

    /**
     * @patternProperties ^[1-5]{1}[0-9]{2}$ Schema\Response
     * @var stdClass $parameters
     */
    public $responses;

    /**
     * @patternProperties .+ Security\ApiKey|Security\Basic|Security\OAuth2
     * @var stdClass $security
     */
    public $security;
}
