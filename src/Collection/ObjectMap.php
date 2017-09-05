<?php

namespace JsonSchema\Collection;

use Doctrine\Common\Reflection;
use JsonSchema\AbstractCollection;
use JsonSchema\Doctrine;
use JsonSchema\Exception;
use JsonSchema\Primitive;
use ReflectionClass;
use ReflectionProperty;

class ObjectMap extends AbstractCollection
{
    /** @var string $namespace */
    protected $namespace;

    /** @var string $class */
    protected $class;

    /** @var array $imports */
    protected $imports;
    
    /** @var array $properties */
    protected $properties;

    /** @var array $required */
    protected $required;

    /**
     * @param string $class
     * @param array $annotations
     *
     * @throws Exception\InvalidClassName
     */
    public function __construct($class, array $annotations = [])
    {
        try {
            $reflectionClass = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new Exception\InvalidClassName($e->getMessage(), $e->getCode(), $e);
        }

        $classFinder = new Doctrine\AutoloadClassFinder();
        $reflectionParser = new Reflection\StaticReflectionParser($reflectionClass->getName(), $classFinder);

        /** @var ReflectionProperty $property */
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($properties as $property) {
            // For each property go through and pull it's doc comment that fits the regex.
            $success = preg_match_all('/\@\w+(.)*/', $property->getDocComment(), $match);
            if ($success !== false) {
                // Parse the line that we matched.
                $this->parseAnnotations($match[0], $property->getName());
            }
        }

        $this->properties = array();
        $this->required = array();
        $this->namespace = $reflectionClass->getNamespaceName();
        $this->class = $reflectionClass->getShortName();
        $this->imports = $reflectionParser->getUseStatements();

        $this->parseAnnotations($annotations);
    }

    /**
     * @param array $annotations
     * @param string $propertyName
     * 
     * @throws Exception\InvalidType
     */
    protected function parseAnnotations(array $annotations, $propertyName)
    {
        $nullable = false;
        $unusedAnnotations = array();
        $type = null;

        // First scan through the annotation list to filter and add required fields.
        foreach ($annotations as $annotation) {
            /** @var array $parts */
            $parts = preg_split('/\s/', $annotation);
            if ($parts !== false) {
                if ($parts[0] == "@var" && ! $type) {
                    // PHP DOC states that it will be in the form of 0-VAR 1-TYPE 2-NAME 3-DESCRIPTION.
                    if (isset($parts[1]) && $parts[1][0] != "$") {
                        $type = $parts[1];
                    }
                } else if ($parts[0] == "@required") {
                    $this->required[] = $propertyName;
                } else if ($parts[0] == "@nullable") {
                    $nullable = true;
                } else {
                    $unusedAnnotations[] = $annotation;
                }
            }
        }

        if ($type === null) {
            throw new Exception\InvalidType("Primitive is not defined");
        }

        switch ($type) {
            case "string":
                $property = new Primitive\StringType($unusedAnnotations);
                $this->addToProperties($nullable, $propertyName, $property);
                break;

            case "int":
            case "integer":
                $property = new Primitive\IntegerType($unusedAnnotations);
                $this->addToProperties($nullable, $propertyName, $property);
                break;

            case "float":
                $property = new Primitive\NumberType($unusedAnnotations);
                $this->addToProperties($nullable, $propertyName, $property);
                break;

            case "bool":
            case "boolean":
                $property = new Primitive\BooleanType();
                $this->addToProperties($nullable, $propertyName, $property);
                break;

            // Match primitive and object array notation.
            case (preg_match('/[\[](\s)*[\]]/$', $type) == 1):
                $property = null;

                preg_match('/(.)+[^\[\s\]]/', $type, $match);
                if (! isset($match[0])) {
                    throw new Exception\InvalidType("Invalid array notation.");
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
                    $property = new ArrayList($namespace, $unusedAnnotations);
                } else if (in_array($match[0], array("string", "int", "integer", "float", "bool", "boolean"))) {
                    $property = new ArrayList($match[0], $unusedAnnotations);
                } else {
                    throw new Exception\InvalidType("{$match[0]} is not recognized.");
                }

                $this->addToProperties($nullable, $propertyName, $property);
                break;

            case "null":
                $property = new Primitive\NullType();
                $this->properties[$propertyName] = $property;
                break;

            default:
                $namespace = $this->getFullNamespace($type);
                if ($namespace === false) {
                    throw new Exception\AnnotationNotFound("Annotation {$type} not supported.");
                }

                $property = null;
                if ($type === $this->class) {
                    $property = array("\$ref" => "#");
                } else {
                    $property = new ObjectMap($namespace);
                }

                $this->addToProperties($nullable, $propertyName, $property);
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
        $schema = array(
            "type" => "object"
        );

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
