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
     *
     * @throws Exception\InvalidClassName
     */
    public function __construct($class)
    {
        try {
            $reflectionClass = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new Exception\InvalidClassName($e->getMessage(), $e->getCode(), $e);
        }

        $classFinder = new Doctrine\AutoloadClassFinder();
        $reflectionParser = new Reflection\StaticReflectionParser($reflectionClass->getName(), $classFinder);

        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

        $this->properties = array();
        $this->required = array();
        $this->namespace = $reflectionClass->getNamespaceName();
        $this->class = $reflectionClass->getShortName();
        $this->imports = $reflectionParser->getUseStatements();

        $this->processProperties($properties);
    }

    /**
     * @param array $properties
     *
     * @throws Exception\AnnotationNotFound
     */
    protected function processProperties(array $properties)
    {
        $annotations = array();

        /** @var ReflectionProperty $property */
        foreach ($properties as $property) {
            // For each property go through and pull it's doc comment that fits the regex.
            $success = preg_match_all('/\@\w+(.)*/', $property->getDocComment(), $annotations);
            if ($success !== false) {
                // Parse the line that we matched.
                $this->parseAnnotations($annotations[0], $property->getName());
            }
        }
    }

    /**
     * @param array $annotationList
     * @param string $propertyName
     * 
     * @throws Exception\InvalidType
     */
    protected function parseAnnotations(array $annotationList, $propertyName)
    {
        $parsedAnnotations = array();
        $type = null;

        // First scan through the annotation list to filter and add required fields.
        foreach ($annotationList as $annotation) {
            /** @var array $splitAnnotations */
            $splitAnnotations = preg_split('/\s/', $annotation);

            if (in_array("@var", $splitAnnotations) && !$type) {
                // PHP DOC dictates that it will be in the form 0-VAR 1-TYPE 2-NAME 3-DESCRIPTION.
                if (isset($splitAnnotations[1]) && $splitAnnotations[1][0] != "$") {
                    $type = $splitAnnotations[1];
                }
            } else if (in_array("@required", $splitAnnotations)) {
                $this->required[] = $propertyName;
            } else {
                $parsedAnnotations[] = $annotation;
            }

        }

        if ($type === null) {
            throw new Exception\InvalidType("Primitive is not defined");
        }

        $nullable = false;
        // Note: If put in the previous array search then you would cut down on another looping m complexity.
        $nullSet = array_search("@nullable", $parsedAnnotations);
        if ($nullSet !== false) {
            unset($parsedAnnotations[$nullSet]);
            $nullable = true;
        }

        $basicDataTypes = array("string", "int", "integer", "float", "bool", "boolean", "null");

        switch($type) {
            case "string":
                $property = new Primitive\StringType($parsedAnnotations);
                $this->addToProperties($nullable, $propertyName, $property);
                break;

            case "int":
            case "integer":
                $property = new Primitive\IntegerType($parsedAnnotations);
                $this->addToProperties($nullable, $propertyName, $property);
                break;

            case "float":
                $property = new Primitive\NumberType($parsedAnnotations);
                $this->addToProperties($nullable, $propertyName, $property);
                break;

            case "bool":
            case "boolean":
                $property = new Primitive\BooleanType();
                $this->addToProperties($nullable, $propertyName, $property);
                break;

            // Note: Case to match array notation
            case (preg_match('/[\[](\s)*[\]]/', $type) == 1):
                $arrayType = array();
                $property = null;

                // Note: Attempt to match the type of array.
                preg_match('/(.)+[^\[\s\]]/', $type, $arrayType);
                if (! isset($arrayType[0])) {
                    throw new Exception\InvalidType("Need to provide a valid array type.");
                }

                // Prevent infinite recursion.
                if ($arrayType[0] == $this->class) {
                    $property = array(
                        "type" => "array",
                        "items" => array(
                            "\$ref" => "#"
                        )
                    );
                } else if (($namespace = $this->getFullNamespace($arrayType[0])) !== false) {
                    $property = new ArrayList($namespace, $parsedAnnotations);
                } else if (in_array($arrayType[0], $basicDataTypes)) {
                    $property = new ArrayList($arrayType[0], $parsedAnnotations);
                } else {
                    throw new Exception\InvalidType("Primitive {$arrayType[0]} is not recognized.");
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
                    throw new Exception\InvalidType("Primitive {$type} not recognized.");
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
