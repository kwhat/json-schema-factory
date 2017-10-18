<?php

namespace JsonSchema\Template\Swagger\v2_0;

use JsonSchema\AbstractSchema;
use JsonSchema\Template\Swagger\v2_0\Parameter;
use stdClass;

class OpenAPI extends AbstractSchema
{
    /**
     * @required
     * @pattern [0-9]+(\.[0-9]+){,2}
     * @enum 2.0
     * @var string $swagger
     */
    public $swagger = "2.0";

    /**
     * @required
     * @var Schema\Info $info
     */
    public $info;

    /**
     * @var string $host
     */
    public $host;

    /**
     * @var string $basePath
     */
    public $basePath;

    /**
     * @enum http|https|ws|wss
     * @var string[] $schemes
     */
    public $schemes;

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
     * @required
     * @patternProperties ^\/[\w/\-%.]+[^\/]$ v2_0\Path\Item
     * @var stdClass $paths
     */
    public $paths;

    /**
     * @patternProperties ^\/[\w/\-%.]+[^\/]$ SchemaInterface
     * @var stdClass $paths
     */
    public $definitions;

    /**
     * @patternProperties .+ Parameter\Body|Parameter\FormData|Parameter\Header|Parameter\Path|Parameter\Query
     * @var stdClass $parameters
     */
    public $parameters;

    /**
     * @patternProperties .* Schema\Response
     * @var stdClass $responses
     */
    public $responses;

    /**
     * @patternProperties .+ string[]
     * @var stdClass $security
     */
    public $security;

    /**
     * @patternProperties .+ Security\ApiKey|Security\Basic|Security\OAuth2
     * @var stdClass $security
     */
    public $securityDefinitions;

    /**
     * @generic OpenAPI\AbstractParameter
     * @var stdClass $tags
     */
    public $tags;

    /**
     * @var Schema\ExternalDocumentation $externalDocs
     */
    public $externalDocs;
}
