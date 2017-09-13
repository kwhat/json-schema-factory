<?php

namespace JsonSchema\OpenAPI\v2_0;

use JsonSchema\AbstractSchema;
use JsonSchema\Collection;
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
     * @pattern ^\/[\w/\-%.]+[^\/]$
     * @generic v2_0\Path\Item
     * @var stdClass $paths
     */
    public $paths;

    /**
     * @generic Collection\ObjectMap
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
     * @generic Security\ApiKey | Security\Basic | Security\OAuth2
     * @var stdClass $security
     */
    public $securityDefinitions;

    /**
     * @generic OpenAPI\AbstractParameter
     * @var stdClass $tags
     */
    public $tags;

    /**
     * @var ExternalDocumentation $externalDocs
     */
    public $externalDocs;
}
