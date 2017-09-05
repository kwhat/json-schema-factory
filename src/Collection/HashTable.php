<?php

namespace JsonSchema\Collection;

use Doctrine\Common\Reflection;
use JsonSchema\AbstractCollection;
use JsonSchema\Doctrine;
use JsonSchema\Exception;
use JsonSchema\Primitive;
use ReflectionClass;
use ReflectionProperty;

class HashTable extends AbstractCollection
{
    /** @var string $namespace */
    protected $namespace;

    /** @var string $pattern */
    protected $pattern;

    /** @var array $properties */
    protected $properties;

    /** @var array $required */
    protected $required;

    /**
     * @param string[] $annotations
     */
    public function __construct(array $annotations)
    {
        $this->properties = array();

        $this->parseAnnotations($annotations, $property->getName());
    }

    /**
     * @param string $class
     * @param array $annotations
     *
     * @throws Exception\InvalidType
     */
    protected function parseAnnotations(array $annotations = ["@pattern" => '[\w]+'])
    {
        $type = null;
        $required = false;

        // First scan through the annotation list to filter and add required fields.
        foreach ($annotations as $annotation) {
            /** @var string[] $parts */
            $parts = preg_split('/\s/', $annotation);

            if ($parts[0] == "@pattern") {
                if (isset($parts[1])) {
                    $this->pattern = (string) $parts[1];
                }
            } else if ($parts[0] == "@generic" && ! $type) {
                if (isset($parts[1])) {
                    $type = $parts[1];
                }
            } else if ($parts[0] == "@required") {
                $required = true;
            }
        }

        if ($type === null) {
            throw new Exception\AnnotationNotFound("HashTable generic not defined.");
        }

        $primitives = array("string", "int", "integer", "float", "bool", "boolean", "null");

        switch ($type) {
            case "string":
                $property = new Primitive\StringType($parsedAnnotations);
                $this->addToProperties($required, $propertyName, $property);
                break;

            case "int":
            case "integer":
                $property = new Primitive\IntegerType($parsedAnnotations);
                $this->addToProperties($required, $propertyName, $property);
                break;

            case "float":
                $property = new Primitive\NumberType($parsedAnnotations);
                $this->addToProperties($required, $propertyName, $property);
                break;

            case "bool":
            case "boolean":
                $property = new Primitive\BooleanType();
                $this->addToProperties($required, $propertyName, $property);
                break;

            // Match '[]' array notation.
            case (preg_match('/[\[](\s)*[\]]/', $type) == 1):
                $property = null;

                // Attempt to match the type of array.
                preg_match('/(.)+[^\[\s\]]/', $type, $match);
                if (! isset($match[0])) {
                    throw new Exception\InvalidType("Need to provide a valid array type.");
                }

                // Prevent infinite recursion.
                if ($match[0] == $this->class) {
                    $property = array(
                        "type" => "array",
                        "items" => array(
                            "\$ref" => "#"
                        )
                    );
                } else if (($namespace = $this->getFullNamespace($match[0])) !== false) {
                    $property = new ArrayList($namespace, $parsedAnnotations);
                } else if (in_array($match[0], $primitives)) {
                    $property = new ArrayList($match[0], $parsedAnnotations);
                } else {
                    throw new Exception\InvalidType("{$match[0]} is not recognized.");
                }

                $this->addToProperties($required, $propertyName, $property);
                break;

            case "null":
                $property = new Primitive\NullType();
                $this->properties[$propertyName] = $property;
                break;

            default:
                $namespace = $this->getFullNamespace($type);
                if ($namespace === false) {
                    throw new Exception\InvalidType("Primitive {$type} not recognized.");
                }

                $property = null;
                if ($type === $this->class) {
                    $property = array("\$ref" => "#");
                } else {
                    $property = new ObjectMap($namespace);
                }

                $this->addToProperties($required, $propertyName, $property);
        }
    }

    /**
     * @param bool $isNullable
     * @param string $propertyName
     * @param object $property
     */
    private function addToProperties($isNullable, $propertyName, $property)
    {
        if ($isNullable === true) {
            if (in_array($propertyName, $this->required)) {
                $this->properties[$propertyName] = array(
                    "oneOf" => array(
                        new NullType(),
                        $property
                    )
                );
            }
        } else {
            $this->properties[$propertyName] = $property;
        }
    }

    /**
     * @param string $type
     * @return string|false $fullNamespace
     */
    private function getFullNamespace($type)
    {
        $fullNamespace = false;

        if (class_exists("\\" . $this->namespace . "\\" . $type)) {
            $fullNamespace = "\\" . $this->namespace . "\\" . $type;
        } else {
            foreach($this->imports as $useStatement) {
                if (class_exists($useStatement . "\\" . $type)) {
                    $fullNamespace = $useStatement . "\\" . $type;
                    break;
                }
            }
        }

        return $fullNamespace;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $schema = array();
        $schema["type"] = "object";

        if ($this->title !== null) {
            $schema["title"] = $this->title;
        }

        if ($this->description !== null) {
            $schema["description"] = $this->description;
        }

        if ($this->properties !== null) {
            $schema["properties"] = $this->properties;
        }

        if ($this->required !== null) {
            $schema["required"] = $this->required;
        }

        return $schema;
    }
}
