<?php

namespace JsonSchema;

use JsonSchema\Exception;

class ArrayType extends BaseType implements TypeInterface
{
    /** @var bool $additionalItems */
    private $additionalItems;

    /** @var array $items */
    private $items;

    /** @var int $minItems */
    private $minItems;

    /** @var int $maxItems */
    private $maxItems;

    /** @var bool $uniqueItems */
    private $uniqueItems;

    public function __construct($className, array $properties = null)
    {
        $basicDataTypes = array("string", "int", "integer", "float", "bool", "boolean", "null");
        preg_match('/(.)*[^\[\s\]]/', $className, $arrayType);
        if (in_array($arrayType[0], $basicDataTypes)) {
            $this->items = array(
                "type" => $arrayType[0]
            );
        }
        else {
            $this->items = new ObjectType($arrayType[0]);
        }

        if ($properties != null) {
            $this->processProperties($properties);
        }
    }

    /**
     * @param array $properties
     * @throws Exceptions\InvalidTypeException
     * @throws Exceptions\AnnotationNotFound
     */
    protected function processProperties(array $properties)
    {
        foreach($properties as $property)
        {
            $parsedProperty = preg_split('/\s/', $property);
            if (!isset($parsedProperty[0])) {
                throw new Exceptions\InvalidTypeException("Need to provide a keyword to the annotation.");
            }
            if (!isset($parsedProperty[1])) {
                throw new Exceptions\InvalidTypeException("Need to provide a value to the annotation keyword.");
            }
            $annotationKeyword = $parsedProperty[0];
            $annotationValue = $parsedProperty[1];
            switch ($annotationKeyword)
            {
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
                    throw new Exceptions\AnnotationNotFound("Annotation {$annotationKeyword} not recognized.");
            }
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $serializableArray = array();
        $serializableArray["type"] = "array";

        if ($this->title !== null) {
            $serializableArray["title"] = $this->title;
        }

        if ($this->description !== null) {
            $serializableArray["description"] = $this->description;
        }

        if ($this->additionalItems !== null) {
            $serializableArray["additionalItems"] = $this->additionalItems;
        }

        if ($this->items !== null) {
            $serializableArray["items"] = $this->items;
        }

        if ($this->minItems !== null) {
            $serializableArray["minItems"] = $this->minItems;
        }

        if ($this->maxItems !== null) {
            $serializableArray["maxItems"] = $this->maxItems;
        }

        $serializableArray["uniqueItems"] = true;
        return $serializableArray;
    }
}
