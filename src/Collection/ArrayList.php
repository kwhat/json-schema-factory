<?php

namespace JsonSchema\Collection;

use JsonSchema\AbstractCollection;
use JsonSchema\Exception;

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

    public function __construct($class, array $properties = null)
    {
        $this->additionalItems = false;
        
        $type = array();
        if (preg_match('/(.*)[^\[\s\]]/', $class, $type) !== false) {
            switch ($type[0]) {
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
                    $this->items = array("type" => $type[0]);
                    break;

                default:
                    $this->items = new ObjectMap($type[0]);
            }
        }

        $this->uniqueItems = false;

        if ($properties != null) {
            $this->processProperties($properties);
        }
    }

    /**
     * @param array $properties
     *
     * @throws Exception\InvalidType
     * @throws Exception\AnnotationNotFound
     */
    protected function processProperties(array $properties)
    {
        foreach($properties as $property) {
            $parsedProperty = preg_split('/\s/', $property);
            if (! isset($parsedProperty[0])) {
                throw new Exception\InvalidType("Need to provide a keyword to the annotation.");
            }

            if (! isset($parsedProperty[1])) {
                throw new Exception\InvalidType("Need to provide a value to the annotation keyword.");
            }

            $annotationKeyword = $parsedProperty[0];
            $annotationValue = $parsedProperty[1];
            switch ($annotationKeyword) {
                case "@additionalItems":
                    $this->additionalItems = (bool) $annotationValue;
                    break;

                case "@minItems":
                    $this->minItems = (int) $annotationValue;
                    break;

                case "@maxItems":
                    $this->maxItems = (int) $annotationValue;
                    break;

                case "@uniqueItems":
                    $this->uniqueItems = (bool) $annotationValue;
                    break;

                default:
                    throw new Exception\AnnotationNotFound("Annotation {$annotationKeyword} not supported.");
            }
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $schema = array();
        $schema["type"] = "array";

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
                // Additional items should not be used unless there are multipul types.
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
