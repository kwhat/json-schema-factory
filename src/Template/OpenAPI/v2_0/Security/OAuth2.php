<?php

namespace JsonSchema\OpenAPI\v2_0\Security;

use JsonSchema;
use JsonSchema\AbstractSchema;
use JsonSchema\Collection;
use JsonSchema\Factory;
use stdClass;

class OAuth2 extends AbstractSchema
{
    /**
     * @required
     * @enum implicit password application accessCode
     * @var Flow\AccessCode|Flow\Application|Flow\Implicit|Flow\Password $flow
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
