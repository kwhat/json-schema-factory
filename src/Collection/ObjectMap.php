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
     */
    public function __construct($class, array $annotations = [])
    {
        $this->properties = array();
        $this->required = array();
        $this->type = "object";

        $this->parseProperties($class);
        $this->parseAnnotations($annotations);
    }

    /**
     * Parse the properties of this class.
     *
     * @param string $class
     *
     * @throws Exception\ClassNotFound
     * @throws Exception\MalformedAnnotation
     */
    protected function parseProperties($class)
    {
        if (interface_exists($class)) {
            // Assume that interfaces have other annotations set.
            $class = stdClass::class;
        }

        try {
            $reflectionClass = new ReflectionClass($class);
        } catch (ReflectionException $e) {
            throw new Exception\ClassNotFound($e->getMessage(), $e->getCode(), $e);
        }

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
                $match = preg_replace('/(@var)[\s]+([\w\[\]\\\|]+)(\s+[^$]{1}.*|$)/',
                    '$1 $2 \$' . $property->getName() . '${3}', $match);

                // Parse each matched annotation for items that apply to the parent object.
                foreach ($match as &$annotation) {
                    $args = preg_split('/[\s]+/', $annotation, 4);

                    if ($args !== false) {
                        $keyword = array_shift($args);

                        switch ($keyword) {
                            case "@required":
                                // TODO Maybe take an optional second param for property name.
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
                                        $i = 0;
                                    }
                                }
                                $types[] = $args[0];

                                foreach ($types as $type) {
                                    // Try resolving a full namespace.
                                    $namespace = $this->getFullNamespace($type, $reflectionClass);
                                    if ($namespace !== false) {
                                        $type = $namespace;
                                    }

                                    $schema = Factory::create($type, $match);

                                    // Check for a title.
                                    if (preg_match('/^\/\*\*([^@]+)/', $docComment, $title)) {
                                        $title[1] = trim(preg_replace('/[\s*]+/', " ", $title[1]));
                                        if (! empty($title[1])) {
                                            $schema->title = $title[1];
                                        }
                                    }

                                    // Check for a description at the end of the $docComment.
                                    if (preg_match('/[\r]?[\n]{1}([^@]+)\*\/$/', $docComment, $description)) {
                                        $description[1] = trim(preg_replace('/[\s*]+/', " ", $description[1]));
                                        if (! empty($description[1])) {
                                            $schema->description = $description[1];
                                        }
                                    }

                                    // FIXME This should be one of if more than one!
                                    $this->properties[substr($args[1], 1)] = $schema;
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
                            throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                        }

                        $this->maxProperties = (int) $args[0];
                        break;

                    case "@minProperties":
                        if (! isset($args[0])) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                        }

                        $this->minProperties = (int) $args[0];
                        break;

                    case "@patternProperties":
                        if (! isset($args[0]) || ! isset($args[1])) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$annotation}!");
                        }

                        $this->patternProperties[$args[0]] = Factory::create($this->getFullNamespace($args[1]), $annotations);
                        break;
                }
            }
        }
    }

    /**
     * @param string $type
     * @param ReflectionClass|null $parent
     *
     * @return string|false $fullNamespace
     */
    protected function getFullNamespace($type, ReflectionClass $parent = null)
    {
        $fullNamespace = false;

        $isArray = false;
        // FIXME This should be regex?
        if (substr($type, -2) == "[]") {
            $isArray = true;
            $type = substr($type, 0, -2);
        }
        
        if (class_exists($type) || interface_exists($type)) {
            // Check for fully qualified name.
            $fullNamespace = $type;
        } else if ($parent != null) {
            $classFinder = new Doctrine\AutoloadClassFinder();
            $reflectionParser = new Reflection\StaticReflectionParser($parent->getName(), $classFinder);
            $namespace = $parent->getNamespaceName();

            $fullNamespace = $this->getFullNamespace("{$namespace}\\{$type}");
            if (! $fullNamespace) {
                // Check for use statements.
                $imports = array_values($reflectionParser->getUseStatements());
                for ($i = 0; !$fullNamespace && $i < count($imports); $i++) {
                    /** @var string $use */
                    $use = $imports[$i];

                    if (preg_match('/(.+)\s+as\s+' . preg_quote($type, "\\") . '$/', $use, $match)) {
                        // Check for use alias.
                        $fullNamespace = $this->getFullNamespace($match[1]);
                    } else if (preg_match('/(.*)' . preg_quote($type, "\\") . '$/', $use, $match)) {
                        // Check for use ending with.
                        $fullNamespace = $this->getFullNamespace($match[0]);
                    } else {
                        // Check for use relative namespace.
                        $fullNamespace = $this->getFullNamespace("{$use}\\{$type}");

                        if (!$fullNamespace) {
                            // Check for use extension namespace.
                            $separator = strrpos($use, "\\");
                            if ($separator !== false) {
                                $class = substr($use, 0, $separator + 1) . $type;
                                $fullNamespace = $this->getFullNamespace($class);
                            }
                        }
                    }
                }
            }

            if ($fullNamespace === false && ($parent = $parent->getParentClass())) {
                $fullNamespace = $this->getFullNamespace($type, $parent);
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

        /*
        if (is_array($this->properties)) {
            $schema["properties"] = $this->properties;
        } else if (is_string($this->properties)) {
            $schema["patternProperties"] = $this->properties;
        }

        if (! empty($this->required)) {
            $schema["required"] = $this->required;
        }

        if (defined(static::TYPE)) {
            $this->type = static::TYPE;
        }

        $schema = array_filter($schema, function ($property) {
            if (in_array("", $this->re)) {

            }

            return $property !== null;
        });
        */

        return $schema;
    }
}
