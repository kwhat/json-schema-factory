<?php

namespace JsonSchema\Primitive;

use JsonSchema\Exception;
use JsonSchema\TypeInterface;

class Number implements TypeInterface
{
    /** @var int $maximum */
    protected $maximum;

    /** @var int $minimum */
    protected $minimum;

    /** @var int $multipleOf */
    protected $multipleOf;

    /** @var bool $exclusiveMaximum */
    protected $exclusiveMaximum;

    /** @var bool $exclusiveMinimum */
    protected $exclusiveMinimum;

    /**
     * @param array $annotations
     */
    public function __construct(array $annotations = null)
    {
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
        foreach($annotations as $annotation)
        {
            $parts = preg_split('/\s/', $annotation);
            if (! isset($parts[0]) || ! isset($parts[1])) {
                throw new Exception\InvalidType("Invalid annotation format.");
            }

            $keyword = $parts[0];
            $value = $parts[1];
            switch ($keyword)
            {
                case "@exclusiveMinimum":
                    $this->exclusiveMinimum = (bool) $value;
                    break;

                case "@exclusiveMaximum":
                    $this->exclusiveMaximum = (bool) $value;
                    break;

                case "@maximum":
                    $this->maximum = (int) $value;
                    break;

                case "@minimum":
                    $this->minimum = (int) $value;
                    break;

                case "@multipleOf":
                    $this->multipleOf = (int) $value;
                    break;

                default:
                    throw new Exception\AnnotationNotFound("Annotation {$keyword} not recognized.");
            }
        }
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $schema = array(
            "type" => "number"
        );

        if ($this->multipleOf !== null) {
            $schema["multipleOf"] = $this->multipleOf;
        }

        if ($this->maximum !== null) {
            $schema["maximum"] = $this->maximum;
        }

        if ($this->minimum !== null) {
            $schema["minimum"] = $this->minimum;
        }

        if ($this->exclusiveMinimum !== null) {
            $schema["exclusiveMinimum"] = $this->exclusiveMinimum;
        }

        if ($this->exclusiveMaximum !== null) {
            $schema["exclusiveMaximum"] = $this->exclusiveMaximum;
        }

        return $schema;
    }
}
