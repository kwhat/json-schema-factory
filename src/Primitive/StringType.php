<?php

namespace JsonSchema\Primitive;

use JsonSchema\Exception;
use JsonSchema\TypeInterface;

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
        foreach ($annotations as $annotation) {
            $parts = preg_split('/\s/', $annotation);
            if (! isset($parts[0]) || ! isset($parts[1])) {
                throw new Exception\InvalidType("Invalid annotation format.");
            }

            $keyword = $parts[0];
            $value = $parts[1];

            switch ($keyword) {
                case "@enum":
                    $this->enum = array();
                    $enums = array_slice($parts, 1);
                    foreach ($enums as $enum) {
                        // Results from the regex, if successful, will be stored in the array index zero.
                        $match = array();
                        if (preg_match('/[^\,\s]+/', $enum, $match) !== false) {
                            $this->enum[] = $match[0];
                        }
                    }
                    break;

                case "@minLength":
                    $this->minLength = (int) $value;
                    break;

                case "@maxLength":
                    $this->maxLength = (int) $value;
                    break;

                case "@pattern":
                    $this->pattern = (string) $value;
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
            "type" => "string"
        );

        if ($this->minLength !== null) {
            $schema["minLength"] = $this->minLength;
        }

        if ($this->maxLength !== null) {
            $schema["maxLength"] = $this->maxLength;
        }

        if ($this->enum !== null) {
            $schema["enum"] = $this->enum;
        }

        if ($this->pattern !== null) {
            $schema["pattern"] = $this->pattern;
         }

        return $schema;
    }
}
