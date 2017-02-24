<?php

namespace Json\Schema;

use JsonSerializable;

interface TypeInterface extends JsonSerializable
{
    public function jsonSerialize();
}
