<?php

namespace JsonSchema\Template\Swagger\v2_0\Security;

use JsonSchema;
use JsonSchema\AbstractSchema;
use JsonSchema\Collection;
use JsonSchema\Factory;
use JsonSchema\Template\Swagger\v2_0\Security\Flow\AccessCode;
use JsonSchema\Template\Swagger\v2_0\Security\Flow\Application;
use JsonSchema\Template\Swagger\v2_0\Security\Flow\Implicit;
use JsonSchema\Template\Swagger\v2_0\Security\Flow\Password;
use stdClass;

class OAuth2 extends AbstractSchema
{
    /**
     * @required
     * @enum implicit|password|application|accessCode
     * @var AccessCode|Application|Implicit|Password $flow
     */
    public $flow;

    public $authorizationUrl;

    public $tokenUrl;

    /**
     * @pattern [\x21\x23-\x5B\x5D-\x7E]+
     * @generic string
     * @var stdClass $scopes
     */
    public $scopes;

    /**
     * @return JsonSchema\Collection\ObjectMap
     */
    public static function schemaSerialize()
    {
        $schema = Factory::create(static::class);

        return $schema;
    }
}
