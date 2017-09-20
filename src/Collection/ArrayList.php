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

        $types = preg_split('/\|/', $class);

        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        fclose($handle);

        $itmes = array();
        foreach ($types as $type) {
            if (preg_match('/(.*)\[\]/', $type, $match)) {

                if ($match[0] == $class) {
                    /** @var ArrayList $item */
                    $item = Factory::create($match[1]);
                    $item->items = array(
                        "\$ref" => "#"
                    );
                    $this->items[] = $item;
                } else {
                    $this->items[] = Factory::create($match[1], $annotations);
                }
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
