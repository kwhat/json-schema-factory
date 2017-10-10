<?php

namespace JsonSchema\Collection;

use Doctrine\Common\Reflection;
use JsonSchema\AbstractCollection;
use JsonSchema\Doctrine;
use JsonSchema\Exception;
use JsonSchema\Factory;
use JsonSchema\SchemaInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use stdClass;

class ObjectMap extends AbstractCollection
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

        if (interface_exists($class)) {
            // Assume that interfaces have other annotations set.
            $class = stdClass::class;
        }

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
     * Parse the properties of this class.
     *
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
                // TODO This is probably not needed
                $match = preg_replace('/(@var)[\s]+([\w\[\]\\|]+)(\s+[^$]{1}.*|$)/',
                    '$1 $2 \$' . $property->getName() . '${3}', $match);

                // Parse each matched annotation for items that apply to the parent object.
                foreach ($match as $annotation) {
                    $token = preg_split('/[\s]+/', $annotation, 4);

                    if ($token !== false) {
                        $keyword = array_shift($token);

                        switch ($keyword) {
                            case "@required":
                                $this->required[] = $property->getName();
                                break;

                            case "@properties":
                                // Alias for @var
                            case "@var":
                                if (! isset($token[0])) {
                                    throw new Exception\MalformedAnnotation("Malformed annotation {$this->class}::{$annotation}!");
                                }

                                $types = explode("|", $token[0]);
                                foreach ($types as $type) {
                                    if ()
                                }


                                // Try resolving a full namespace.
                                $namespace = $this->getFullNamespace($token[0]);
                                if ($namespace !== false) {
                                    $token[0] = $namespace;
                                }
                                var_dump($namespace, $token[0]);

                                $type = Factory::create($token[0], $match);

                                // Check for a title.
                                if (preg_match('/^[\s*]+[^@]/', $docComment, $match)) {
                                    $type->title = $match[0];
                                }

                                // Check for a description at the end of the $docComment.
                                if (preg_match_all('/[^@].*$/s', $docComment, $match)) {
                                    $type->description = $match[0];
                                }

                                $this->properties[$property->getName()] = $type;
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
            $token = preg_split('/[\s]+/', $annotation, 4);

            if ($token !== false) {
                $keyword = array_shift($token);

                switch ($keyword) {
                    case "@additionalProperties":
                        if (isset($token[0])) {
                            $this->additionalProperties[] = $this->getFullNamespace($token[0]);
                        } else {
                            $this->additionalProperties = true;
                        }
                        break;

                    case "@maxProperties":
                        if (! isset($token[0])) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$this->class}::{$annotation}!");
                        }

                        $this->maxProperties = (int) $token[0];
                        break;

                    case "@minProperties":
                        if (! isset($token[0])) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$this->class}::{$annotation}!");
                        }

                        $this->minProperties = (int) $token[0];
                        break;

                    case "@patternProperties":
                        if (! isset($token[0]) || ! isset($token[1])) {
                            throw new Exception\MalformedAnnotation("Malformed annotation {$this->class}::{$annotation}!");
                        }

                        $this->patternProperties[$token[0]] = Factory::create($this->getFullNamespace($token[1]), $annotations);
                        break;
                }
            }
        }
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
