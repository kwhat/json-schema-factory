<?php

namespace JsonSchema\Primitive;

use JsonSchema\Exception;
use JsonSchema\TypeInterface;

class IntegerType implements TypeInterface
{
    /** @var array $enum */
    protected $enum;

    /** @var bool $exclusiveMaximum */
    protected $exclusiveMaximum;

    /** @var bool $exclusiveMinimum */
    protected $exclusiveMinimum;

    /** @var int $maximum */
    protected $maximum;

    /** @var int $minimum */
    protected $minimum;

    /** @var float $multipleOf */
    protected $multipleOf;

    /**
     * @param array $annotations
     */
    public function __construct(array $annotations = [])
    {
        $this->exclusiveMaximum = false;
        $this->exclusiveMinimum = false;

        $this->parseAnnotations($annotations);
    }

    /**
     * @param string[] $annotations
     *
     * @throws Exception\MalformedAnnotation
     */
    protected function parseAnnotations(array $annotations)
    {
        foreach($annotations as $annotation) {
            $parts = preg_split('/\s/', $annotation);
            if ($parts !== false) {
                $keyword = array_shift($parts);

                switch ($keyword) {
                    case "@enum":
                        if (empty($parts)) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                        }

                        $this->enum = $parts;
                        break;

                    case "@exclusiveMaximum":
                        $this->exclusiveMaximum = true;
                        break;

                    case "@exclusiveMinimum":
                        $this->exclusiveMinimum = true;
                        break;

                    case "@maximum":
                        if (! isset($parts[0])) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                        }

                        $this->maximum = (int) $parts[0];
                        break;

                    case "@minimum":
                        if (! isset($parts[0])) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                        }

                        $this->minimum = (int) $parts[0];
                        break;

                    case "@multipleOf":
                        if (! isset($parts[0])) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                        }

                        $this->multipleOf = (float) $parts[0];
                        break;
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
            "type" => "integer"
        );

        if ($this->enum !== null) {
            $schema["enum"] = $this->enum;
        }

        if ($this->minimum !== null) {
            $schema["minimum"] = $this->minimum;

            if ($this->exclusiveMinimum) {
                $schema["exclusiveMinimum"] = $this->exclusiveMinimum;
            }
        }

        if ($this->maximum !== null) {
            $schema["maximum"] = $this->maximum;

            if ($this->exclusiveMaximum) {
                $schema["exclusiveMaximum"] = $this->exclusiveMaximum;
            }
        }

        if ($this->multipleOf !== null) {
            $schema["multipleOf"] = $this->multipleOf;
        }

        return $schema;
    }
}
