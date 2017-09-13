<?php

namespace JsonSchema\Primitive;

use JsonSchema\Exception;
use JsonSchema\TypeInterface;

class StringType implements TypeInterface
{
    /** @var string[]|null $enum */
    protected $enum;

    /** @var int|null $maxLength */
    protected $maxLength;

    /** @var int|null $minLength */
    protected $minLength;

    /** @var string|null $pattern */
    protected $pattern;
    
    public function __construct(array $annotations = [])
    {
        $this->parseAnnotations($annotations);
    }

    /**
     * @param string[] $annotations
     *
     * @throws Exception\MalformedAnnotation
     */
    protected function parseAnnotations(array $annotations)
    {
        foreach ($annotations as $annotation) {
            $parts = preg_split('/\s/', $annotation, 2);

            if (! isset($parts[0]) || ! isset($parts[1])) {
                throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
            } else {
                $keyword = array_shift($parts);

                switch ($keyword) {
                    case "@enum":
                        $enums = preg_split('/\s/', $parts[0]);
                        if ($enums !== false) {
                            $this->enum = $enums;
                        }
                        break;

                    case "@minLength":
                        $this->minLength = (int) $parts[0];
                        break;

                    case "@maxLength":
                        $this->maxLength = (int) $parts[0];
                        break;

                    case "@pattern":
                        $this->pattern = (string) $parts[0];
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
