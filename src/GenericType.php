<?php

namespace Bildr\Json\Schema;

use JsonSerializable;

interface GenericType extends JsonSerializable
{
    public function jsonSerialize();
}
