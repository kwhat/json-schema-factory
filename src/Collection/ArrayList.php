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
            $parts = preg_split('/[\s]+/', $annotation, 2);

            if ($parts !== false) {
                $keyword = array_shift($parts);

                switch ($keyword) {
                    case "@additionalItems":
                        if (! isset($parts[0]) || $parts[0] == "true" || $parts[0] == true) {
                            $this->additionalItems = true;
                        }
                        break;

                    case "@collectionFormat":
                        // TODO implement
                        break;

                    case "@maxItems":
                        if (! isset($parts[0])) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                        }

                        $this->maxItems = (int) $parts[0];
                        break;

                    case "@minItems":
                        if (! isset($parts[0])) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                        }

                        $this->minItems = (int) $parts[0];
                        break;

                    case "@uniqueItems":
                        if (! isset($parts[0]) || $parts[0] == "true" || $parts[0] == true) {
                            $this->uniqueItems = true;
                        }
                        break;
                }
            }
        }
    }
}
