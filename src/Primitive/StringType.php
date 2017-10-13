<?php

namespace JsonSchema\Primitive;

use JsonSchema\AbstractSchema;
use JsonSchema\Exception;

class StringType extends AbstractSchema
{
    /**
     * @enum binary|byte|date|date-time|password
     * @var string $format
     */
    public $format;

    /**
     * @var int $maxLength 
     */
    public $maxLength;

    /** 
     * @var int $minLength 
     */
    public $minLength;

    /** 
     * @var string $pattern 
     */
    public $pattern;

    /**
     * @param string[] $annotations
     */
    public function __construct(array $annotations = [])
    {
        $this->type = "string";
        $this->parseAnnotations($annotations);
    }

    /**
     * @param string[] $annotations
     *
     * @throws Exception\MalformedAnnotation
     */
    protected function parseAnnotations(array $annotations)
    {
        parent::parseAnnotations($annotations);

        foreach ($annotations as $annotation) {
            $args = preg_split('/[\s]+/', $annotation, 2);

            $keyword = array_shift($args);
            switch ($keyword) {
                case "@enum":
                    if (! isset($args[0])) {
                        throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                    }

                    $this->enum = explode("|", $args[0]);
                    break;

                case "@minLength":
                    if (! isset($args[0])) {
                        throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                    }

                    $this->minLength = (int) $args[0];
                    break;

                case "@maxLength":
                    if (! isset($args[0])) {
                        throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                    }

                    $this->maxLength = (int) $args[0];
                    break;

                case "@pattern":
                    if (! isset($args[0])) {
                        throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                    }

                    $this->pattern = (string) $args[0];
                    break;
            }
        }
    }
}
