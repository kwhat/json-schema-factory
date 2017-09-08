<?php

namespace JsonSchema\Collection;

use Doctrine\Common\Reflection;
use JsonSchema\AbstractCollection;
use JsonSchema\Doctrine;
use JsonSchema\Exception;
use JsonSchema\Factory;
use JsonSchema\TypeInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use stdClass;

class ObjectMap extends AbstractCollection
{
    /** @var string $class */
    protected $class;

    /** @var string $namespace */
    protected $namespace;

    /** @var string[] $imports */
    protected $imports;

    /** @var TypeInterface[]|string $properties */
    protected $properties;

    /** @var array $required */
    protected $required;

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

        if ($class == stdClass::class) {
            $this->class = stdClass::class;
            $this->namespace = "\\";
            $this->imports = array();

            // Parse the line that we matched, order matters.
            $parsed = $this->parseAnnotations($annotations);
            $this->properties = '[\w]+';
            if (isset($parsed["@pattern"]) && isset($parsed["@pattern"][0])) {
                $this->properties = $parsed["@pattern"][0];
            }

            if (isset($parsed["@generic"]) && isset($parsed["@generic"][0])) {
                $this->class = $parsed["@pattern"][0];
            }
        } else {
            try {
                $reflectionClass = new ReflectionClass($class);
            } catch (ReflectionException $e) {
                echo $e->getTraceAsString();
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
        }

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
            if (preg_match('/^\w+/', $docComment, $match)) {
                $this->setDescription($match[0]);
            }

            // Match all properties.
            if (preg_match_all('/(@\w+)[^\S\x0a\x0d]?(.*)$/m', $docComment, $match)) {
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

                        $properties[] = Factory::create($type, null, null, $annotations);
                    }

                    $count = count($properties);
                    if ($count == 1) {
                        $type = $properties[0];
                    } else if ($count > 1) {
                        $type = array("oneOf" => $properties);
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
     * @param string $type
     * @return string|false $fullNamespace
     */
    private function getFullNamespace($type)
    {
        $fullNamespace = false;

        if (class_exists("\\" . $this->namespace . "\\" . $type)) {
            $fullNamespace = "\\" . $this->namespace . "\\" . $type;
        } else {
            foreach($this->imports as $use) {
                // Check for use alias.
                if (preg_match('/(.+)\s+as\s+' . preg_quote($type, "\\") . '/', $use, $match)) {
                    if (class_exists($match[1])) {
                        $fullNamespace = $match[1];
                        break;
                    }
                } else if (class_exists("{$use}\\{$type}")) {
                    $fullNamespace = "{$use}\\{$type}";
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
        $schema = array(
            "type" => "object"
        );

        if ($this->title !== null) {
            $schema["title"] = $this->title;
        }

        if ($this->description !== null) {
            $schema["description"] = $this->description;
        }

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
