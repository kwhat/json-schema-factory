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
            $parts = preg_split('/\s/', $annotation);

            $keyword = array_shift($parts);

            switch ($keyword) {
                case "@enum":
                    if (empty($parts)) {
                        throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                    }

                    $this->enum = $parts;
                    break;

                case "@minLength":
                    if (! isset($parts[0])) {
                        throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                    }

                    $this->minLength = (int) $parts[0];
                    break;

                case "@maxLength":
                    if (! isset($parts[0])) {
                        throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                    }

                    $this->maxLength = (int) $parts[0];
                    break;

                case "@pattern":
                    if (! isset($parts[0])) {
                        throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                    }

                    $this->pattern = (string) $parts[0];
                    break;
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

        if ($this->enum !== null) {
            $schema["enum"] = $this->enum;
        }

        if ($this->maxLength !== null) {
            $schema["maxLength"] = $this->maxLength;
        }

        if ($this->minLength !== null) {
            $schema["minLength"] = $this->minLength;
        }

        if ($this->pattern !== null) {
            $schema["pattern"] = $this->pattern;
         }

        return $schema;
    }
}
