<?php

namespace JsonSchema\Template\OpenAPI\v2_0\Schema\Path\Item;

use JsonSchema\Template\OpenAPI\v2_0\Parameter;
use JsonSchema\Template\OpenAPI\v2_0\Schema;
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
     * @var string[] $consumes
     */
    public $consumes;

    /**
     * @var string[] $produces
     */
    public $produces;

    /**
     * @patternProperties [\w]+ Parameter\Body
     * @var stdClass $parameters
     */
    public $parameters;

    /**
     * @var string[] $parameters
     */
    public $security;
}
