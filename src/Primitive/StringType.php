<?php

namespace JsonSchema\Primitive;

use JsonSchema\TypeInterface;

class StringType implements TypeInterface
{
    /** @var int|null $minLength */
    protected $minLength;

    /** @var array|null $enum */
    protected $enum;

    /** @var int|null $maxLength */
    protected $maxLength;

    /** @var string|null $pattern */
    protected $pattern;
    
    public function __construct(array $annotations = [])
    {
        $this->parseAnnotations($annotations);
    }

    /**
     * @param string[] $annotations
     */
    protected function parseAnnotations(array $annotations)
    {
        foreach ($annotations as $annotation) {
            $parts = preg_split('/\s/', $annotation, 2);
            if (! isset($parts[0]) || ! isset($parts[1])) {
                trigger_error("Malformed annotation {$annotation}!", E_USER_WARNING);
            } else {
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
