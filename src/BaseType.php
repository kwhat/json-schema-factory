<?php

namespace Bildr\Json\Schema;

class BaseType
{
    /** @var string|null $title */
    protected $title;

    /** @var string|null $description */
    protected $description;

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }
}
