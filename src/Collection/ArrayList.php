<?php

namespace JsonSchema\Collection;

use JsonSchema\AbstractSchema;
use JsonSchema\Exception;
use JsonSchema\Factory;

class ArrayList extends AbstractSchema
{
    const TYPE = "array";

    /**
     * @enum csv ssv tsv pipes
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
        $this->items = array();
        $this->uniqueItems = false;

        if (preg_match('/([\w\\]+)\[(string(\|int)?)?(int(\|string)?)?\]$/', $class, $match)) {
            $match = array_filter($match);
            $this->items[] = Factory::create($match[1], $annotations);

            if (isset($match[3]) && strpos($match[3], "string") !== false) {

            }
        }

        if (! isset($this->items[0])) {
            throw new Exception\MalformedAnnotation("Missing annotation @var!");
        }

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
            $parts = preg_split('/[\s]+/', $annotation, 2);

            if ($parts !== false) {
                $keyword = array_shift($parts);

                switch ($keyword) {
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
                        $this->uniqueItems = true;
                        break;
                }
            }
        }
    }
}
