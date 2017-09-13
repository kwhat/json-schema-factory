<?php

namespace JsonSchema\Primitive;

use JsonSchema\TypeInterface;

class IntegerType implements TypeInterface
{
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
        $this->parseAnnotations($annotations);
    }

    /**
     * @param array $annotations
     */
    protected function parseAnnotations(array $annotations)
    {
        foreach($annotations as $annotation) {
            $parts = preg_split('/\s/', $annotation);
            if ($parts !== false) {
                $keyword = array_shift($parts);

                switch ($keyword) {
                    case "@exclusiveMaximum":
                        $this->exclusiveMaximum = true;
                        break;

                    case "@exclusiveMinimum":
                        $this->exclusiveMinimum = true;
                        break;

                    default:
                        // Process keyword arguments.
                        if (! isset($parts[0])) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                        }

                        switch ($keyword) {
                            case "@maximum":
                                $this->maximum = (float) $parts[0];
                                break;

                            case "@minimum":
                                $this->minimum = (float) $parts[0];
                                break;

                            case "@multipleOf":
                                $this->multipleOf = (float) $parts[0];
                                break;
                        }
                }
            }
            
            if ($parts !== false) {
                $keyword = array_shift($parts);
                switch ($keyword) {
                    case "@enum":
                        if (! empty($parts)) {
                            $this->enum = $parts;
                        }
                        break;

                    case "@exclusiveMinimum":
                        $this->exclusiveMinimum = true;
                        break;

                    case "@exclusiveMaximum":
                        $this->exclusiveMaximum = true;
                        break;

                    case "@maximum":
                        $this->maximum = (int) $parts[0];
                        break;

                    case "@minimum":
                        $this->minimum = (int) $parts[0];
                        break;

                    case "@multipleOf":
                        $this->multipleOf = (float) $parts[0];
                        break;
                }
            }


            if (!isset($parts[0]) || !isset($parts[1])) {
                trigger_error("Malformed annotation {$annotation}!", E_USER_WARNING);
            } else {
                $keyword = $parts[0];
                $value = $parts[1];
                switch ($keyword) {

                    



                    default:
                        trigger_error("Unknown annotation {$keyword}!", E_USER_NOTICE);
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

        if ($this->multipleOf !== null) {
            $schema["multipleOf"] = $this->multipleOf;
        }

        if ($this->minimum !== null) {
            $schema["minimum"] = $this->minimum;
        }

        if ($this->maximum !== null) {
            $schema["maximum"] = $this->maximum;
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
