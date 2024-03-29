<?php

namespace JsonSchema\Primitive;

use JsonSchema\AbstractSchema;
use JsonSchema\Exception;

class NumberType extends AbstractSchema
{
    /**
     * @var bool $exclusiveMaximum
     */
    public $exclusiveMaximum;

    /**
     * @var bool $exclusiveMinimum
     */
    public $exclusiveMinimum;

    /**
     * @enum float double
     * @var string $format
     */
    public $format;

    /**
     * @var float $maximum
     */
    public $maximum;

    /**
     * @var float $minimum
     */
    public $minimum;

    /** 
     * @var float $multipleOf 
     */
    public $multipleOf;

    /**
     * @param string[] $annotations
     */
    public function __construct(array $annotations = [])
    {
        $this->exclusiveMaximum = false;
        $this->exclusiveMinimum = false;
        $this->type = "number";
        
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

                    $this->maximum = (float) $args[0];
                    break;

                case "@minimum":
                    if (! isset($args[0])) {
                        throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                    }

                    $this->minimum = (float) $args[0];
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

        if (! isset($schema["maximum"]) || ! $this->exclusiveMaximum) {
            unset($schema["exclusiveMaximum"]);
        }

        if (! isset($schema["minimum"]) || ! $this->exclusiveMinimum) {
            unset($schema["exclusiveMinimum"]);
        }

        return $schema;
    }
}
