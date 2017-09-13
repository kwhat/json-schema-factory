<?php

namespace JsonSchema\Collection;

use JsonSchema\AbstractCollection;
use JsonSchema\Exception;
use JsonSchema\Factory;
use JsonSchema\TypeInterface;

class ArrayList extends AbstractCollection
{
    /** @var TypeInterface[] $items */
    protected $items;

    /** @var int|null $maxItems */
    protected $maxItems;

    /** @var int|null $minItems */
    protected $minItems;

    /** @var bool $uniqueItems */
    protected $uniqueItems;

    /**
     * ArrayList constructor.
     *
     * @param string $class
     * @param string[] $annotations
     */
    public function __construct($class, array $annotations = [])
    {
        $this->uniqueItems = false;
        $this->items = array();

        $types = preg_split('/\s?\|\s?/', $class);
        foreach ($types as $type) {
            if (preg_match('/(.*)\[\s?\]/', $type, $match)) {
                $this->items[] = Factory::create($match[1], null, null, $annotations);
            }
        }

        $this->parseAnnotations($annotations);
    }

    /**
     * @param array $annotations
     *
     * @throws Exception\MalformedAnnotation
     */
    protected function parseAnnotations(array $annotations)
    {
        foreach ($annotations as $annotation) {
            $parts = preg_split('/\s/', $annotation, 2);

            if ($parts !== false) {
                $keyword = array_shift($parts);

                switch ($keyword) {
                    case "@uniqueItems":
                        $this->uniqueItems = true;
                        break;

                    default:
                        if (! isset($parts[0])) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                        }

                        switch ($keyword) {
                            case "@maxItems":
                                $this->maxItems = (int) $parts[0];
                                break;

                            case "@minItems":
                                $this->minItems = (int) $parts[0];
                                break;
                        }
                }
            }
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $schema = array(
            "type" => "array"
        );

        if ($this->title !== null) {
            $schema["title"] = $this->title;
        }

        if ($this->description !== null) {
            $schema["description"] = $this->description;
        }

        if (! empty($this->items)) {
            $schema["items"] = $this->items;
        }

        if ($this->minItems !== null) {
            $schema["minItems"] = $this->minItems;
        }

        if ($this->maxItems !== null) {
            $schema["maxItems"] = $this->maxItems;
        }

        $schema["uniqueItems"] = $this->uniqueItems;

        return $schema;
    }
}
