<?php

namespace JsonSchema\Template\Swagger\v2_0;

use JsonSchema\AbstractSchema;
use JsonSchema\Template\Swagger\v2_0\Parameter;
use stdClass;

class OpenAPI extends AbstractSchema
{
    /**
     * The version of this OpenAPI document, do not change.
     *
     * @required
     * @pattern [0-9]+(\.[0-9]+){,2}
     * @enum 2.0
     * @var string $swagger
     */
    public $swagger = "2.0";

    /**
     * The info sub-document for this group of endpoints.
     *
     * @required
     * @var Schema\Info $info
     */
    public $info;

    /**
     * The hostname for this service.
     *
     * @var string $host
     */
    public $host;

    /**
     * The base uri for this group of endpoints.
     *
     * @var string $basePath
     */
    public $basePath;

    /**
     * The protocols used to access this group of endpoints.
     *
     * @enum http|https|ws|wss
     * @var string[] $schemes
     */
    public $schemes;

    /**
     * The mime-types accepted by this group of endpoints.
     *
     * @uniqueItems
     * @minItems 1
     * @pattern [a-zA-Z\-]+/[a-zA-Z\-]+
     * @var string[] $consumes
     */
    public $consumes;

    /**
     * The mime-types produced by this group of endpoints.
     *
     * @uniqueItems
     * @minItems 1
     * @pattern ^[a-zA-Z0-9\-]+\/[a-zA-Z0-9\-]+
     * @var string[] $produces
     */
    public $produces;

    /**
     * An object map of uri patterns to an endpoint item object.
     *
     * @required
     * @patternProperties ^\/[\w/\-%.]+[^\/]$ v2_0\Schema\Path\Item
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
     * @patternProperties ^[1-5][0-9]{2}$ Schema\Response
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
     * @patternProperties .+ OpenAPI\AbstractParameter
     * @var stdClass $tags
     */
    public $tags;

    /**
     * A hyperlink to an external documentation resource.
     * @var Schema\ExternalDocumentation $externalDocs
     */
    public $externalDocs;
}
