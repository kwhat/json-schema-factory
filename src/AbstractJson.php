<?php

namespace Bildr\Json\Schema;

use Exception;
use JsonSerializable;

abstract class AbstractJson implements JsonSerializable
{
    final public function __construct(array $properties = [])
    {
        foreach ($properties as $key => $value) {
            $this->{$key} = $value;
        }
    }

    final public function __get($name)
    {
        throw new Exception("Invalid property {$name}");
    }

    final public function __set($name, $value)
    {
        throw new Exception("Invalid property {$name}");
    }

    final public function jsonSerialize()
    {
        return $this;
    }
}
