<?php

namespace Bildr\Json\Schema;

use Doctrine\Common\Reflection;
use Bildr\PoPoGadget\Doctrine;
use Bildr\PoPoGadget\Exceptions;
use ReflectionClass;
use ReflectionProperty;

class ObjectType extends BaseType implements GenericType
{
    /** @var array $properties */
    private $properties;

    /** @var  string $nameSpace */
    private $namespace;

    /** @var  array $required */
    private $required;

    /** @var string $className */
    private $className;

    /** @var array $useStatements */
    private $useStatements;

    /**
     * @param string $class
     */
    public function __construct($class)
    {
        $this->properties = array();
        $this->required = array();

        $reflectedClass = new ReflectionClass($class);
        $this->namespace = $reflectedClass->getNamespaceName();
        $propertyList = $reflectedClass->getProperties(ReflectionProperty::IS_PUBLIC);
        $this->className = $reflectedClass->getShortName();
        $fileFinder = new Doctrine\Psr4FileFinder();
        $reflectionParser = new Reflection\StaticReflectionParser($reflectedClass->getName(), $fileFinder);
        $this->useStatements = $reflectionParser->getUseStatements();

        $this->processProperties($propertyList);
    }

    /**
     * @param array $propertyList
     * @throws Exceptions\AnnotationNotFound
     */
    protected function processProperties(array $propertyList)
    {
        $annotationList = array();

        /** @var ReflectionProperty $property */
        foreach ($propertyList as $property) {
            // Note: For each property go through and pull it's doc comment that fits the regex.
            $regexSuccess = preg_match_all('/\@\w+(.)*/', $property->getDocComment(), $annotationList);
            if ($regexSuccess) {
                $annotationsForParsing = $annotationList[0];
                $propertyName = $property->getName();
                // Note: Parse the line that we matched.
                $this->parseAnnotationList($annotationsForParsing, $propertyName);
            }
        }
    }

    /**
     * @param array $annotationList
     * @param string $propertyName
     * @throws Exceptions\InvalidTypeException
     */
    protected function parseAnnotationList(array $annotationList, $propertyName)
    {
        $parsedAnnotations = array();
        $type = null;

        // Note: First scan through the annotation list to filter and add required fields.
        foreach ($annotationList as $annotation) {

            // Note: Split on spaces.
            /** @var array $splitAnnotations */
            $splitAnnotations = preg_split('/\s/', $annotation);

            if (in_array('@var', $splitAnnotations) && !$type) {
                // Note: PHP DOC dictates that it will be in the form 0-VAR 1-TYPE 2-NAME 3-DESCRIPTION.
                if (isset($splitAnnotations[1]) && $splitAnnotations[1][0] != "$") {
                    $type = $splitAnnotations[1];
                }
            } else if (in_array('@required', $splitAnnotations)) {
                $this->required[] = $propertyName;
            } else {
                $parsedAnnotations[] = $annotation;
            }

        }

        if ($type === null) {
            throw new Exceptions\InvalidTypeException("Type is not defined");
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
                $property = new StringType($parsedAnnotations);
                $this->addToProperties($nullable, $propertyName, $property);
                break;

            case "int":
            case "integer":
                $property = new IntegerType($parsedAnnotations);
                $this->addToProperties($nullable, $propertyName, $property);
                break;

            case "float":
                $property = new NumberType($parsedAnnotations);
                $this->addToProperties($nullable, $propertyName, $property);
                break;

            case "bool":
            case "boolean":
                $property = new BooleanType();
                $this->addToProperties($nullable, $propertyName, $property);
                break;

            // Note: Case to match array notation
            case (preg_match('/[\[](\s)*[\]]/', $type) == 1):
                $arrayType = array();
                $property = null;

                // Note: Attempt to match the type of array.
                preg_match('/(.)*[^\[\s\]]/', $type, $arrayType);
                if (!isset($arrayType[0])) {
                    throw new Exceptions\InvalidTypeException("Need to provide a valid array type.");
                }

                // Note: Don't want to recurse infinitely.
                if ($arrayType[0] == $this->className) {
                    $property = array(
                        "type" => "array",
                        "items" => array(
                            "\$ref" => "#"
                        )
                    );
                } else if (($namespace = $this->getFullNamespace($arrayType[0])) !== false) {
                    $property = new ArrayType($namespace, $parsedAnnotations);
                } else if (in_array($arrayType[0], $basicDataTypes)) {
                    $property = new ArrayType($arrayType[0], $parsedAnnotations);
                } else {
                    throw new Exceptions\InvalidTypeException("Type {$arrayType[0]} is not recognized.");
                }

                $this->addToProperties($nullable, $propertyName, $property);
                break;

            case "null":
                $property = new NullType();
                $this->properties[$propertyName] = $property;
                break;

            default:
                $namespace = $this->getFullNamespace($type);
                if ($namespace === false) {
                    throw new Exceptions\InvalidTypeException("Type {$type} not recognized.");
                }

                $property = null;
                if ($type === $this->className) {
                    $property = array("\$ref" => "#");
                } else {
                    $property = new ObjectType($namespace);
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
     * @return false|string $fullNamespace
     */
    private function getFullNamespace($type)
    {
        $fullNamespace = false;

        if (class_exists("\\" . $this->namespace . "\\" . $type)) {
            $fullNamespace = "\\" . $this->namespace . "\\" . $type;
        } else {
            foreach($this->useStatements as $useStatement) {
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
        $serializableArray = array();
        $serializableArray["type"] = "object";

        if ($this->title !== null) {
            $serializableArray["title"] = $this->title;
        }

        if ($this->description !== null) {
            $serializableArray["description"] = $this->description;
        }

        if ($this->properties !== null) {
            $serializableArray["properties"] = $this->properties;
        }

        if ($this->required !== null) {
            $serializableArray["required"] = $this->required;
        }

        return $serializableArray;
    }

}
