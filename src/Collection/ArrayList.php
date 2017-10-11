<?php

namespace JsonSchema\Collection;

use JsonSchema\AbstractSchema;
use JsonSchema\Exception;
use JsonSchema\Factory;

class ArrayList extends AbstractSchema
{
    const TYPE = "array";

    /**
     * @var boolean $additionalItems
     */
    public $additionalItems;

    /**
     * @enum csv|ssv|tsv|pipes
     * @var string $collectionFormat
     */
    public $collectionFormat;

    /**
     * @required
     * @var AbstractSchema[] $items
     */
    public $items;

    /**
     * @minimum 0
     * @var int $maxItems
     */
    public $maxItems;

    /**
     * @minimum 0
     * @var int $minItems
     */
    public $minItems;

    /**
     * @var bool $uniqueItems
     */
    public $uniqueItems;

    /**
     * @param string $class
     * @param string[] $annotations
     *
     * @throws Exception\MalformedAnnotation
     */
    public function __construct($class, array $annotations = [])
    {
        $this->items = array(
            Factory::create($class, $annotations)
        );
        $this->uniqueItems = false;

        $this->parseAnnotations($annotations);
    }

    /**
     * @param string[] $annotations
     *
     * @throws Exception\MalformedAnnotation
     */
    protected function parseAnnotations(array $annotations)
    {
        // We must call the parent parser to handle upstream annotations.
        parent::parseAnnotations($annotations);

        foreach ($annotations as $annotation) {
            $args = preg_split('/[\s]+/', $annotation, 2);

            if ($args !== false) {
                $keyword = array_shift($args);

                switch ($keyword) {
                    case "@additionalItems":
                        if (! isset($args[0]) || $args[0] == "true" || $args[0] == true) {
                            $this->additionalItems = true;
                        }
                        break;

                    case "@collectionFormat":
                        // FIXME implement
                        break;

                    case "@maxItems":
                        if (! isset($args[0])) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                        }

                        $this->maxItems = (int) $args[0];
                        break;

                    case "@minItems":
                        if (! isset($args[0])) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                        }

                        $this->minItems = (int) $args[0];
                        break;

                    case "@uniqueItems":
                        if (! isset($args[0]) || $args[0] == "true" || $args[0] == true) {
                            $this->uniqueItems = true;
                        }
                        break;
                }
            }
        }
    }
}
