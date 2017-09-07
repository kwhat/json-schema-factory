<?php

namespace JsonSchema;

abstract class AbstractCollection implements TypeInterface
{
    /** @var string|null $title */
    protected $title;

    /** @var string|null $description */
    protected $description;

    /**
     * @param string $class
     * @param string[] $annotations
     */
    abstract public function __construct($class, array $annotations = []);

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
}
