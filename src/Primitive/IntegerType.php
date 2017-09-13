<?php

namespace JsonSchema\Primitive;

use JsonSchema\AbstractSchema;
use JsonSchema\Exception;

class IntegerType extends AbstractSchema
{
    /** @var int[] $enum */
    public $enum;

    /** @var bool $exclusiveMaximum */
    public $exclusiveMaximum;

    /** @var bool $exclusiveMinimum */
    public $exclusiveMinimum;

    /** @var int $maximum */
    public $maximum;

    /** @var int $minimum */
    public $minimum;

    /** @var float $multipleOf */
    public $multipleOf;

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
            $args = preg_split('/\s/', $annotation);

            $keyword = array_shift($args);
            switch ($keyword) {
                case "@enum":
                    if (empty($args)) {
                        throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                    }

                    $this->enum = $args;
                    break;

                case "@exclusiveMaximum":
                    $this->exclusiveMaximum = true;
                    break;

                case "@exclusiveMinimum":
                    $this->exclusiveMinimum = true;
                    break;

                case "@maximum":
                    if (! isset($args[0])) {
                        throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                    }

                    $this->maximum = (int) $args[0];
                    break;

                case "@minimum":
                    if (! isset($args[0])) {
                        throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                    }

                    $this->minimum = (int) $args[0];
                    break;

                case "@multipleOf":
                    if (! isset($args[0])) {
                        throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                    }

                    $this->multipleOf = (float) $args[0];
                    break;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        $schema = parent::jsonSerialize();
        $schema["type"] = "integer;";

        if (! isset($schema["maximum"]) || ! $this->exclusiveMaximum) {
            unset($schema["exclusiveMaximum"]);
        }

        if (! isset($schema["minimum"]) || ! $this->exclusiveMinimum) {
            unset($schema["exclusiveMinimum"]);
        }

        return $schema;
    }
}
