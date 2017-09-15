<?php

namespace JsonSchema\Collection;

use Doctrine\Common\Reflection;
use JsonSchema\AbstractSchema;
use JsonSchema\Doctrine;
use JsonSchema\Exception;
use JsonSchema\Factory;
use JsonSchema\Primitive\BooleanType;
use JsonSchema\SchemaInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use stdClass;

class ObjectMap extends AbstractSchema
{
    const TYPE = "object";

    /**
     * @var BooleanType|SchemaInterface $additionalProperties
     */
    public $additionalProperties;

    /**
     * @minimum 0
     * @var int $maxProperties
     */
    public $maxProperties;

    /**
     * @minimum 0
     * @var int $minProperties
     */
    public $minProperties;

    /**
     * @additionalProperties SchemaInterface
     * @patternProperties .*
     * @var ObjectMap $patternProperties
     */
    public $patternProperties;

    /**
     * @additionalProperties SchemaInterface
     * @patternProperties [\w-]+
     * @var ObjectMap $properties
     */
    public $properties;

    /**
     * @minLength 1
     * @var string[] $required
     */
    public $required;


    /** @var string $class */
    protected $class;

    /** @var string $namespace */
    protected $namespace;

    /** @var string[] $imports */
    protected $imports;

    /**
     * @param string $class
     * @param string[] $annotations
     *
     * @throws Exception\ClassNotFound
     */
    public function __construct($class, array $annotations = [])
    {
        $this->properties = array();
        $this->required = array();

        try {
            $reflectionClass = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new Exception\ClassNotFound($e->getMessage(), $e->getCode(), $e);
        }

        $classFinder = new Doctrine\AutoloadClassFinder();
        $reflectionParser = new Reflection\StaticReflectionParser($reflectionClass->getName(), $classFinder);

        $this->class = $reflectionClass->getShortName();
        $this->namespace = $reflectionClass->getNamespaceName();
        $this->imports = $reflectionParser->getUseStatements();

        /** @var ReflectionProperty $property */
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);
        $this->parseProperties($properties);

        $this->parseAnnotations($annotations);
    }

    /**
     * @param ReflectionProperty[] $properties
     *
     * @throws Exception\MalformedAnnotation
     */
    protected function parseProperties(array $properties)
    {
        foreach ($properties as $property) {
            /** @var string $docComment */
            $docComment = $property->getDocComment();

            // Check for a description.
            if (preg_match('/^[^@]+/', $docComment, $match)) {
                $this->description($match[0]);
            }

            // Match all annotations in the docComment.
            if (preg_match_all('/(@[\w+\-])[^\S\x0a\x0d]?(.*)$/m', $docComment, $match)) {
                $tags = array_combine($match[1], $match[2]);

                if (isset($tags["@required"])) {
                    $this->required[] = $property->getName();
                    unset($tags["@required"]);
                }

                if (isset($tags["@var"])) {
                    $args = preg_split('/\s/', $tags["@var"], 3);
                    unset($tags["@var"]);
                    if (empty($args)) {
                        throw new Exception\MalformedAnnotation("Malformed annotation for {$this->class}::{$property}!");
                    }

                    $schemas = array();
                    $annotations = array_map(function ($key, $value) {
                        return trim("{$key} {$value}");
                    }, array_keys($tags), array_values($tags));

                    // Get multiple types.
                    $types = preg_split('/\s?\|\s?/', $args[0]);
                    foreach ($types as $type) {
                        $namespace = $this->getFullNamespace($type);
                        if ($namespace !== false) {
                            $type = $namespace;
                        }

                        $schemas[] = Factory::create($type, $annotations);
                    }

                    $count = count($schemas);
                    if ($count == 1) {
                        $type = $schemas[0];
                    } else if ($count > 1) {
                        $type = array("oneOf" => $schemas);
                    } else {
                        $type = Factory::create("null");
                    }

                    $this->properties[$property->getName()] = $type;
                }
            }
        }
    }

    /**
     * @param array $annotations
     *
     * @return array tag => [args]
     */
    protected function parseAnnotations(array $annotations = [])
    {
        $parsed = array();
        foreach ($annotations as $annotation) {
            /** @var string[] $parts */
            $parts = preg_split('/\s/', $annotation);
            if ($parts !== false) {
                /** @var string $tag */
                $tag = array_shift($parts);
                $parsed[$tag] = $parts;
            }
        }

        return $parsed;
    }


    /**
     * @param string[] $annotations
     *
     * @throws Exception\MalformedAnnotation
     */
    protected function parseAnnotations(array $annotations)
    {
        foreach ($annotations as $annotation) {
            $parts = preg_split('/\s/', $annotation, 2);

            if ($parts !== false) {
                $keyword = array_shift($parts);

                switch ($keyword) {
                    case "@additionalProperties":
                        if (! isset($parts[0])) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                        }

                        $this->additionalProperties = $this->getFullNamespace($parts[0]);
                        break;

                    case "@maxProperties":
                        if (! isset($parts[0])) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                        }

                        $this->minItems = (int) $parts[0];
                        break;

                    case "@minProperties":
                        if (! isset($parts[0])) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                        }

                        $this->minItems = (int) $parts[0];
                        break;

                    case "@patternProperties":
                        $this->patternProperties = Factory::create("object", $annotations);

                        /**
                         * @additionalProperties SchemaInterface
                         * @patternProperties .*
                         * @var ObjectMap
                         */

                        /**
                         * @additionalProperties SchemaInterface
                         * @patternProperties [\w-]+
                         * @var ObjectMap $properties
                         */
                    case "@properties":

                        /**
                         * @minLength 1
                         * @var string[] $required
                         */
                    case "@required":

                }
            }
        }
    }


    /**
     * @param string $type
     *
     * @return string|false $fullNamespace
     */
    private function getFullNamespace($type)
    {
        $fullNamespace = false;

        if (class_exists($type)) {
            $fullNamespace = $type;
        } else if (class_exists($this->namespace . "\\" . $type)) {
            $fullNamespace = $this->namespace . "\\" . $type;
        } else {
            foreach($this->imports as $use) {
                // Check for use alias.
                if (preg_match('/(.+)\s+as\s+' . preg_quote($type, "\\") . '$/', $use, $match)) {
                    if (class_exists($match[1])) {
                        $fullNamespace = $match[1];
                        break;
                    }
                } else if (class_exists("{$use}\\{$type}")) {
                    $fullNamespace = "{$use}\\{$type}";
                    break;
                } else {
                    $separator = strrpos($use , "\\");
                    if ($separator !== false) {
                        $class = substr($use, 0, $separator + 1) . $type;
                        if (class_exists($class)) {
                            $fullNamespace = $class;
                            break;
                        }
                    }
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
        $schema = parent::jsonSerialize();

        if (is_array($this->properties)) {
            $schema["properties"] = $this->properties;
        } else if (is_string($this->properties)) {
            $schema["patternProperties"] = $this->properties;
        }

        if (! empty($this->required)) {
            $schema["required"] = $this->required;
        }

        return $schema;
    }
}
