<?php

namespace JsonSchema\Collection;

use Doctrine\Common\Reflection;
use JsonSchema\AbstractCollection;
use JsonSchema\Doctrine;
use JsonSchema\Exception;
use JsonSchema\Primitive;
use ReflectionClass;
use ReflectionProperty;
use stdClass;

class ObjectMap extends AbstractCollection
{
    /** @var string $class */
    protected $class;

    /** @var string $namespace */
    protected $namespace;

    /** @var array $imports */
    protected $imports;

    /** @var string $pattern */
    private $pattern;

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
    public function __construct($class = stdClass::class, array $annotations = ['@pattern [\w]+', '@generic \stdClass'])
    {
        $this->properties = array();
        $this->required = array();

        if ($class == stdClass::class) {
            $this->class = "stdClass";
            $this->namespace = "\\";
            $this->imports = array();
        } else {
            try {
                $reflectionClass = new ReflectionClass($class);
            } catch (ReflectionException $e) {
                throw new Exception\InvalidClassName($e->getMessage(), $e->getCode(), $e);
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

            // For each property go through and pull it's doc comment that fits the regex.
            if (preg_match_all('/@\w+(.)*/', $docComment, $match)) {
                $type = null;

                // Parse the line that we matched.
                foreach ($match[0] as $annotation) {
                    /** @var string[] $parts */
                    $parts = preg_split('/\s/', $annotation);
                    if ($parts !== false) {
                        switch ($parts[0]) {
                            case "@var":
                                if (isset($parts[1])) {

                                }
                        }

                        if ($parts[0] == "@var" &&  && isset($parts[1])) {
                            // PHP DOC states that @var will be in the form of 0-VAR 1-TYPE 2-NAME 3-DESCRIPTION.
                            if (! isset($parts[2])) {
                                trigger_error("Malformed annotation for {$this->class}::{$property}!", E_USER_WARNING);
                            }

                            $type = $parts[1];

                            if (isset($parts[3])) {

                            }
                        } else if ($parts[0] == "@generic") {

                        } else if ($parts[0] == "@required") {
                            $this->required[] = $property->getName();
                        } else {
                            $unusedAnnotations[] = $annotation;
                        }
                    }
                }

                $this->parseAnnotations($match[0], $property->getName());
            }
        }
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

        // First scan through the annotation list to filter and add required fields.
        foreach ($annotations as $annotation) {
            /** @var string[] $parts */
            $parts = preg_split('/\s/', $annotation);
            if ($parts !== false) {
                if ($parts[0] == "@var" && ! $type && isset($parts[1])) {
                    // PHP DOC states that @var will be in the form of 0-VAR 1-TYPE 2-NAME 3-DESCRIPTION.
                    $type = $parts[1];
                } else if ($parts[0] == "@pattern") {

                } else if ($parts[0] == "@required") {
                    $this->required[] = $property->getName();
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

        /** @var string[] $parts */
        $parts = preg_split('/\s?|\s?/', $type);
        foreach ($parts as $type) {

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

            case "double":
            case "float":
                $property = new Primitive\NumberType($unusedAnnotations);
                $this->addToProperties($nullable, $propertyName, $property);
                break;

            case "bool":
            case "boolean":
                $property = new Primitive\BooleanType();
                $this->addToProperties($nullable, $propertyName, $property);
                break;

            case "null":
                $property = new Primitive\NullType();
                $this->properties[$propertyName] = $property;
                break;

            // Match primitive and object array notation.
            case preg_match('/(.)+[^\[\s\]]/', $type, $match) == 1:
                $property = null;

                // Prevent infinite recursion.
                if ($match[0] == $this->class) {
                    $property = array(
                        "type" => "array",
                        "items" => array(
                            "\$ref" => "#"
                        )
                    );
                } else if (in_array($match[1], array("string", "int", "integer", "double", "float", "bool", "boolean"))) {
                    $property = new ArrayList($match[0], $unusedAnnotations);
                } else if (($class = $this->getFullNamespace($match[0])) !== false) {
                    $property = new ArrayList($class, $unusedAnnotations);
                } else {
                    throw new Exception\InvalidType("{$match[0]} is not recognized.");
                }

                $this->addToProperties($nullable, $propertyName, $property);
                break;

            case stdClass::class:
                $class = $type;
                $pattern = '[\w]+';
                foreach ($unusedAnnotations as $annotation) {
                    if (preg_match('/^@pattern[\b]+(.)$/', $annotation, $match) && isset($match[1])) {
                        $pattern = $match[1];
                    } else if (preg_match('/^@generic[\b]+(.)$/', $annotation, $match) && isset($match[1])) {
                        $class = $this->getFullNamespace($match[1]);

                        if ($class === false) {
                            throw new Exception\AnnotationNotFound("Annotation {$type} not supported.");
                        }
                    }
                }

                $property = null;
                if ($type === $this->class) {
                    $property = array("\$ref" => "#");
                } else {
                    $property = new ObjectMap($class);
                }

                $this->addToProperties($nullable, $propertyName, $property);
                break;

            default:
                $class = $this->getFullNamespace($type);
                if ($class === false) {
                    throw new Exception\AnnotationNotFound("Annotation {$type} not supported.");
                }

                $property = null;
                if ($type === $this->class) {
                    $property = array("\$ref" => "#");
                } else {
                    $property = new ObjectMap($class);
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
            foreach($this->imports as $use) {
                // Check for use alias.
                if (preg_match('(.*)/\b+as\b+' . preg_quote($type, "\\") . '/', $use, $match)) {
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

        if ($this->properties !== null) {
            $schema["properties"] = $this->properties;
        } else if (is_string($this->properties)) {
            $schema["patternProperties"] = array($this->properties => );
        }

        if ($this->required !== null) {
            $schema["required"] = $this->required;
        }

        return $schema;
    }
}
