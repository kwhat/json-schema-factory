<?php

namespace JsonSchema\Collection;

use Doctrine\Common\Reflection;
use JsonSchema\AbstractSchema;
use JsonSchema\Doctrine;
use JsonSchema\Exception;
use JsonSchema\Factory;
use JsonSchema\SchemaInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use stdClass;

class ObjectMap extends AbstractSchema
{
    const TYPE = "object";

    /**
     * @var boolean|SchemaInterface $additionalProperties
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
     * @patternProperties .* SchemaInterface
     * @var stdClass $patternProperties
     */
    public $patternProperties;

    /**
     * @patternProperties [\w]+ SchemaInterface
     * @var stdClass $properties
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

            // Match all annotations in the docComment.
            if (preg_match_all('/@[\w]+.*$/m', $docComment, $match)) {
                // Normalize @var annotations to @var <type> $var <comment> format.
                $match = preg_replace('/(@var)[\s]+(\$[\w]+)[\s]+([\w\[\]\\|]+)/', '$1 $3 $2', $match[0]);

                // Append any implied properties to @var annotations.
                $match = preg_replace('/(@var)[\s]+([\w\[\]\\|]+)(\s+[^$]{1}.*|$)/', '$1 $2 \$' . $property->getName() . '${3}', $match);

                foreach ($match as $annotation) {
                    $token = preg_split('/[\s]+/', $annotation, 4);

                    if ($token !== false) {
                        $keyword = array_shift($token);
                        $required = false;

                        switch ($keyword) {
                            case "@required":
                                $required = true;
                                break;

                            case "@properties":
                                // Alias for @var
                            case "@var":
                                break;
                        }
                }

                $this->parseAnnotations($match);
            }

            // Check for a title.
            if (preg_match('/^[\s*]+[^@]/', $docComment, $match)) {
                $this->title = $match[0];
            }

            // Check for a description at the end of the $docComment.
            if (preg_match_all('/[^@].*$/s', $docComment, $match)) {
                $this->description = $match[0];
            }
        }
    }

    /**
     * @param string[] $annotations
     *
     * @throws Exception\MalformedAnnotation
     */
    protected function parseAnnotations(array $annotations)
    {
        foreach ($annotations as $annotation) {
            $token = preg_split('/[\s]+/', $annotation, 4);

            if ($token !== false) {
                $keyword = array_shift($token);
                $required = false;

                switch ($keyword) {
                    case "@additionalProperties":
                        if (! isset($token[0])) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$this->class}::{$annotation}!");
                        }

                        $this->additionalProperties = $this->getFullNamespace($token[0]);
                        break;

                    case "@maxProperties":
                        if (! isset($token[0])) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$this->class}::{$annotation}!");
                        }

                        $this->minItems = (int) $token[0];
                        break;

                    case "@minProperties":
                        if (! isset($token[0])) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$this->class}::{$annotation}!");
                        }

                        $this->minItems = (int) $token[0];
                        break;

                    case "@patternProperties":
                        // FIXME This should take something in the following format:
                        // "patternProperties": {
                        //     "^S_": { "type": "string" },
                        //     "^I_": { "type": "integer" }
                        //  }
                        $this->patternProperties[$token[0]] = Factory::create($this->getFullNamespace($token[1]), $annotations);
                        break;

                    case "@required":
                        $required = true;
                        break;

                    case "@properties":
                        // Alias for @var
                    case "@var":
                        if (! isset($token[0]) || ! isset($token[1])) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$this->class}::{$annotation}!");
                        }

                        // Get multiple types, skipping string[int|string] notation.
                        /*
                        $bracketSentinal = false;
                        foreach ((array) $token[0] as $char) {
                            switch ($char) {
                                case ']':
                                case '[':
                                    $bracketSentinal = ! $bracketSentinal;
                                    break;

                                case '|':
                                    if (! $bracketSentinal) {

                                    }
                            }


                            $namespace = $this->getFullNamespace();
                            if ($namespace !== false) {
                                $type = $namespace;
                            }
                        }
                        */

                        $types = preg_split('/(?<!\[string|\[int)\|/', $token[0]);
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
                            /* TODO oneOf attribute is still confusing at this stage....
                            $type = Factory::create(stdClass::class, array(
                                "@oneOf " . implode("|", $schemas)
                            ));
                            */
                            $type = array("oneOf" => $schemas);
                        } else {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$this->class}::{$annotation}!");
                        }

                        $this->properties[substr($token[1], 1)] = $type;
                        break;
                }
            }
        }
    }

    /**
     * @param string $type
     *
     * @return string|false $fullNamespace
     */
    protected function getFullNamespace($type)
    {
        $fullNamespace = false;

        $isArray = false;
        if (substr($type, -2) == "[]") {
            $isArray = true;
            $type = substr($type, 0, -2);
        }

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
                } else if (preg_match('/(.*)' . preg_quote($type, "\\") . '$/', $use, $match)) {
                    if (class_exists($match[0])) {
                        $fullNamespace = $match[0];
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

        if ($fullNamespace && $isArray) {
            $fullNamespace .= "[]";
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
