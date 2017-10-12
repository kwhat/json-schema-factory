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
    //protected $class;

    /** @var string $namespace */
    //protected $namespace;

    /** @var string[] $imports */
    //protected $imports;

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

        if (interface_exists($class)) {
            // Assume that interfaces have other annotations set.
            $class = stdClass::class;
        }

        try {
            $reflectionClass = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new Exception\ClassNotFound($e->getMessage(), $e->getCode(), $e);
        }

        $this->parseProperties($reflectionClass);
        $this->parseAnnotations($annotations);
    }

    /**
     * Parse the properties of this class.
     *
     * @param ReflectionClass $reflectionClass
     *
     * @throws Exception\MalformedAnnotation
     */
    protected function parseProperties(ReflectionClass $reflectionClass)
    {
        $classFinder = new Doctrine\AutoloadClassFinder();
        $reflectionParser = new Reflection\StaticReflectionParser($reflectionClass->getName(), $classFinder);

        $class = $reflectionClass->getShortName();
        $namespace = $reflectionClass->getNamespaceName();
        $imports = $reflectionParser->getUseStatements();
        $reflectionClass->getParentClass();

        /** @var ReflectionProperty $property */
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            /** @var string $docComment */
            $docComment = $property->getDocComment();

            // Match all annotations in the docComment.
            if (preg_match_all('/@[\w]+.*$/m', $docComment, $match)) {

                // Normalize @var annotations to @var <type> $var <comment> format.
                $match = preg_replace('/(@var)[\s]+(\$[\w]+)[\s]+([\w\[\]\\|]+)/', '$1 $3 $2', $match[0]);

                // Append any implied properties to @var annotations.
                // TODO This is probably not needed
                $match = preg_replace('/(@var)[\s]+([\w\[\]\\|]+)(\s+[^$]{1}.*|$)/',
                    '$1 $2 \$' . $property->getName() . '${3}', $match);

                // Parse each matched annotation for items that apply to the parent object.
                foreach ($match as &$annotation) {
                    $args = preg_split('/[\s]+/', $annotation, 4);

                    if ($args !== false) {
                        $keyword = array_shift($args);

                        switch ($keyword) {
                            case "@required":
                                $this->required[] = $property->getName();
                                break;

                            case "@patternProperties":
                                if (! isset($args[0]) || ! isset($args[1])) {
                                    throw new Exception\MalformedAnnotation("Malformed annotation {$class}::{$annotation}!");
                                }

                                // Try resolving a full namespace.
                                $namespace = $this->getFullNamespace($args[1]);
                                if ($namespace !== false) {
                                    $args[1] = $namespace;
                                }
                                $annotation = implode(" ", $args);
                                break;

                            case "@properties":
                                // Alias for @var
                            case "@var":
                                if (! isset($args[0])) {
                                    throw new Exception\MalformedAnnotation("Malformed annotation {$class}::{$annotation}!");
                                }

                                $types = array();
                                $bracketSentinel = false;
                                for ($i = 0; $i < strlen($args[0]); $i++) {
                                    if ($args[0][$i] == "[") {
                                        $bracketSentinel = true;
                                    } else if ($args[0][$i] == "]") {
                                        $bracketSentinel = false;
                                    } else if ($args[0][$i] == "|" && ! $bracketSentinel) {
                                        $types[] = substr($args[0], 0, $i);
                                        $args[0] = substr($args[0], $i + 1);
                                    }
                                }
                                $types[] = $args[0];

                                foreach ($types as $type) {
                                    // Try resolving a full namespace.
                                    $namespace = $this->getFullNamespace($type);
                                    if ($namespace !== false) {
                                        $type = $namespace;
                                    }

                                    var_dump($namespace, $class, $type);
                                    echo "\n";
                                    $schema = Factory::create($type, $match);

                                    // Check for a title.
                                    if (preg_match('/^[\s*]+[^@]/', $docComment, $title)) {
                                        $schema->title = $title[0];
                                    }

                                    // Check for a description at the end of the $docComment.
                                    if (preg_match_all('/[^@].*$/s', $docComment, $title)) {
                                        $schema->description = $title[0];
                                    }

                                    // FIXME This should be one of if more than one!
                                    $this->properties[$property->getName()] = $schema;
                                }
                                break;
                        }
                    }
                }
            }
        }
    }

    /**
     * Parse the annotations for this class.
     *
     * @param string[] $annotations
     *
     * @throws Exception\MalformedAnnotation
     */
    protected function parseAnnotations(array $annotations)
    {
        // We must call the parent parser to handle upstream annotations.
        parent::parseAnnotations($annotations);

        foreach ($annotations as $annotation) {
            $args = preg_split('/[\s]+/', $annotation, 4);

            if ($args !== false) {
                $keyword = array_shift($args);

                switch ($keyword) {
                    case "@additionalProperties":
                        if (isset($args[0])) {
                            $this->additionalProperties[] = $this->getFullNamespace($args[0]);
                        } else {
                            $this->additionalProperties = true;
                        }
                        break;

                    case "@maxProperties":
                        if (! isset($args[0])) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$this->class}::{$annotation}!");
                        }

                        $this->maxProperties = (int) $args[0];
                        break;

                    case "@minProperties":
                        if (! isset($args[0])) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$this->class}::{$annotation}!");
                        }

                        $this->minProperties = (int) $args[0];
                        break;

                    case "@patternProperties":
                        if (! isset($args[0]) || ! isset($args[1])) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$this->class}::{$annotation}!");
                        }

                        $this->patternProperties[$args[0]] = Factory::create($this->getFullNamespace($args[1]), $annotations);
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

        if (class_exists($type) || interface_exists($type)) {
            // Check for fully qualified name.
            $fullNamespace = $type;
        } else if (class_exists("{$this->namespace}\\{$type}") || interface_exists("{$this->namespace}\\{$type}")) {
            // Check for relative namespace.
            $fullNamespace = "{$this->namespace}\\{$type}";
        } else {
            // Check for use statements.
            foreach($this->imports as $use) {
                if (preg_match('/(.+)\s+as\s+' . preg_quote($type, "\\") . '$/', $use, $match)) {
                    // Check for use alias.
                    if (class_exists($match[1]) || interface_exists($match[1])) {
                        $fullNamespace = $match[1];
                        break;
                    }
                } else if (preg_match('/(.*)' . preg_quote($type, "\\") . '$/', $use, $match)) {
                    // Check for use ending with.
                    if (class_exists($match[0]) || interface_exists($match[0])) {
                        $fullNamespace = $match[0];
                        break;
                    }
                } else if (class_exists("{$use}\\{$type}") || interface_exists("{$use}\\{$type}")) {
                    // Check for use relative namespace.
                    $fullNamespace = "{$use}\\{$type}";
                    break;
                } else {
                    // Check for use extension namespace.
                    $separator = strrpos($use , "\\");
                    if ($separator !== false) {
                        $class = substr($use, 0, $separator + 1) . $type;
                        if (class_exists($class) || interface_exists($class)) {
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
