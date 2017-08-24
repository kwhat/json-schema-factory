<?php

namespace JsonSchema;

abstract class AbstractCollection implements TypeInterface
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
