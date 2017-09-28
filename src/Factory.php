<?php

namespace JsonSchema;

class Factory
{
    /**
     * Track definitions by schema path key.
     *
     * @var AbstractSchema[string] $definitions
     */
    protected static $definitions = array();

    /**
     * @param string $class
     * @param string[] $annotations
     *
     * @return AbstractSchema
     * @throws Exception\ClassNotFound
     */
    public static function create($class, array $annotations = [])
    {
        echo "Factory create {$class} [" . implode(", ", $annotations) . "]\n";

        switch ($class) {
            case "string":
                $schema = new Primitive\StringType($annotations);
                break;

            case "int":
            case "integer":
                $schema = new Primitive\IntegerType($annotations);
                break;

            case "double":
            case "float":
                $schema = new Primitive\NumberType($annotations);
                break;

            case "bool":
            case "boolean":
                $schema = new Primitive\BooleanType();
                break;

            case "null":
                $schema = new Primitive\NullType();
                break;

            case "mixed":
                $schema = "*";
                break;

            // Match primitive and object array notation.
            case preg_match('/^([\w\\]+)\[([\w|]*)\]$/', $class, $match) == 1:
                if (strpos($match[2], "string") !== false) {
                    $class = stdClass::class;
                }

                if (empty($match[2]) || strpos($match[2], "int") !== false) {

                }

                static::$definitions[$match[1]] = null;

                $schema = new Collection\ArrayList($match[1], $annotations);
                break;

            default:
                static::$definitions[$class] = null;
                $schema = new Collection\ObjectMap($class, $annotations);
        }

        /** @var AbstractSchema $schema */
        return $schema;
    }
}
