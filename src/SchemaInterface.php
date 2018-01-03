<?php

namespace JsonSchema;

use JsonSerializable;

interface SchemaInterface extends JsonSerializable
{
    /**
     * Specify data which should be serialized to JSON.
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return JsonSerializable
     */
    public function jsonSerialize();

    /**
     * Specify data which should be serialized to represent this class as a JSON schema.
     * @link https://github.com/OAI/OpenAPI-Specification
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     *
     * @return SchemaInterface
     */
    public static function schemaSerialize();
}
