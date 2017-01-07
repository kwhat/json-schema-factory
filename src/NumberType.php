<?php

namespace Bildr\Json\Schema;

use Bildr\PoPoGadget\Exceptions;

class NumberType implements GenericType
{
    /** @var int $multipleOf */
    private $multipleOf;

    /** @var int $maximum */
    private $maximum;

    /** @var int $minimum */
    private $minimum;

    /** @var bool $exclusiveMaximum */
    private $exclusiveMaximum;

    /** @var bool $exclusiveMinimum */
    private $exclusiveMinimum;

    /**
     * @param array $properties
     */
    public function __construct(array $properties)
    {
        $this->processProperties($properties);
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
                case "@multipleOf":
                    $this->multipleOf = (int) $annotationValue;
                    break;

                case "@minimum":
                    $this->maximum = (int) $annotationValue;
                    break;

                case "@maximum":
                    $this->minimum = (int) $annotationValue;
                    break;

                case "@exclusiveMinimum":
                    $this->exclusiveMinimum = (bool) $annotationValue;
                    break;

                case "@exclusiveMaximum":
                    $this->exclusiveMaximum = (bool) $annotationValue;
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
        $serializableArray["type"] = "number";

        if ($this->multipleOf !== null) {
            $serializableArray["multipleOf"] = $this->multipleOf;
        }

        if ($this->maximum !== null) {
            $serializableArray["maximum"] = $this->maximum;
        }

        if ($this->minimum !== null) {
            $serializableArray["minimum"] = $this->minimum;
        }

        if ($this->exclusiveMinimum !== null) {
            $serializableArray["exclusiveMinimum"] = $this->exclusiveMinimum;
        }

        if ($this->exclusiveMaximum !== null) {
            $serializableArray["exclusiveMaximum"] = $this->exclusiveMaximum;
        }

        return $serializableArray;
    }
}
