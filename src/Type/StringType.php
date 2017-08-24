<?php

namespace JsonSchema\Type;

use JsonSchema\Exception;

class StringType implements TypeInterface
{
    /** @var int|null $minLength */
    private $minLength;

    /** @var array|null $enum */
    private $enum;

    /** @var int|null $maxLength */
    private $maxLength;

    /** @var string|null $pattern */
    private $pattern;
    
    public function __construct($properties)
    {
        $this->processproperties($properties);
    }

    /**
     * @param array $properties
     *
     * @throws Exception\InvalidTypeException
     * @throws Exception\AnnotationNotFound
     */
    protected function processProperties(array $properties)
    {
        foreach ($properties as $property) {
            // TODO Check to see if preg split actually succeeded.
            $parsedProperty = preg_split('/\s/', $property);
            if (!isset($parsedProperty[0])) {
                throw new Exception\InvalidTypeException("Need to provide a keyword to the annotation.");
            }

            if (!isset($parsedProperty[1])) {
                throw new Exception\InvalidTypeException("Need to provide a value to the annotation keyword.");
            }

            $annotationKeyword = $parsedProperty[0];
            $annotationValue = $parsedProperty[1];

            switch ($annotationKeyword)
            {
                case "@minLength":
                    $this->minLength = (int) $annotationValue;
                    break;

                case "@enum":
                    $this->enum = array();
                    $enumList = array_slice($parsedProperty, 1);
                    foreach ($enumList as $enum) {
                        // Results from the regex, if successful, will be stored in the array index zero.
                        $match = array();
                        if (preg_match('/[^\,\s]+/', $enum, $match)) {
                            $this->enum[] = $match[0];
                        }
                    }
                    break;

                case "@maxLength":
                    $this->maxLength = (int) $annotationValue;
                    break;

                case "@pattern":
                    $this->pattern = (string) $annotationValue;
                    break;

                default:
                    throw new Exception\AnnotationNotFound("Annotation {$annotationKeyword} not recognized.");
            }
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $serializableArray = array();
        $serializableArray["type"] = "string";

        if ($this->minLength !== null) {
            $serializableArray["minLength"] = $this->minLength;
        }

        if ($this->maxLength !== null) {
            $serializableArray["maxLength"] = $this->maxLength;
        }

        if ($this->enum !== null) {
            $serializableArray["enum"] = $this->enum;
        }

        if ($this->pattern !== null) {
            $serializableArray["pattern"] = $this->pattern;
         }

        return $serializableArray;
    }
}
