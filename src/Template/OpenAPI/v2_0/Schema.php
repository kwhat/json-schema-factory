<?php

namespace JsonSchema\Template\OpenAPI\v2_0;

use JsonSchema\AbstractSchema;
use stdClass;

class Schema extends AbstractSchema
{
    /**
     * @required
     * @pattern [0-9]+\.[0-9]+\.[0-9]+
     * @var string $swagger
     */
    public $swagger;

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
     * @pattern ^\/[\w/\-%.]+[^\/]$ v2_0\Path\Item
     * @var stdClass $paths
     */
    public $paths;

    /**
     * @pattern ^\/[\w/\-%.]+[^\/]$ v2_0\Schema\Path\Item
     * @var stdClass $paths
     */
    public $definitions;

    public $parameters;

    public $responses;

    /**
     * @generic string[]
     * @var stdClass $security
     */
    public $security;

    /**
     * @var Security\ApiKey|Security\Basic|Security\OAuth2 $security
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
