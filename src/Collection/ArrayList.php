<?php

namespace JsonSchema\Collection;

use JsonSchema\AbstractCollection;
use JsonSchema\Exception;
use stdClass;

class ArrayList extends AbstractCollection
{
    /** @var bool $additionalItems */
    protected $additionalItems;

    /** @var array $items */
    protected $items;

    /** @var int $minItems */
    protected $minItems;

    /** @var int $maxItems */
    protected $maxItems;

    /** @var bool $uniqueItems */
    protected $uniqueItems;

    public function __construct($class, array $annotations = null)
    {
        $this->additionalItems = false;
        $this->uniqueItems = false;
        
        if (preg_match('/(.*)[^\[\s\]]/', $class, $match) !== false) {
            switch ($match[0]) {
                case "int":
                case "integer":
                    $this->items = array("type" => "integer");
                    break;

                case "bool":
                case "boolean":
                    $this->items = array("type" => "boolean");
                    break;

                case "float":
                    $this->items = array("type" => "number");
                    break;

                case "string":
                case "null":
                    $this->items = array("type" => $match[0]);
                    break;

                case stdClass::class:
                    $this->items = new HashTable($annotations);
                    break;

                default:
                    $this->items = new ObjectMap($match[0]);
            }
        }

        if ($annotations != null) {
            $this->parseAnnotations($annotations);
        }
    }

    /**
     * @param array $annotations
     *
     * @throws Exception\InvalidType
     * @throws Exception\AnnotationNotFound
     */
    protected function parseAnnotations(array $annotations)
    {
        foreach($annotations as $annotation) {
            $parts = preg_split('/\s/', $annotation);
            if (! isset($parts[0]) || ! isset($parts[1])) {
                throw new Exception\InvalidType("Invalid annotation format.");
            }

            $keyword = $parts[0];
            $value = $parts[1];

            switch ($keyword) {
                case "@additionalItems":
                    $this->additionalItems = (bool) $value;
                    break;

                case "@minItems":
                    $this->minItems = (int) $value;
                    break;

                case "@maxItems":
                    $this->maxItems = (int) $value;
                    break;

                case "@uniqueItems":
                    $this->uniqueItems = (bool) $value;
                    break;

                default:
                    throw new Exception\AnnotationNotFound("Annotation {$keyword} not supported.");
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

        if ($this->items !== null) {
            $schema["items"] = $this->items;

            // Single elements will have type set while arrays will have a list of associative arrays with type set.
            if (! isset($this->items["type"])) {
                // Additional items should not be used unless there are multiple types.
                $schema["additionalItems"] = $this->additionalItems;
            }
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
