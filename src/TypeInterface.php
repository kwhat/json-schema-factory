<?php

namespace JsonSchema;

use JsonSerializable;

interface TypeInterface extends JsonSerializable
{
    public function jsonSerialize();
}
