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
     * @throws Exception\MalformedAnnotation
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

            // Match primitive and object array notation with optional string|int keys.
            case preg_match('/^([\w\\]+)\[(.*)\]$/', $class, $match) == 1:
                $keySchemas = array();
                $keyTypes = explode("|", $match[2]);
                foreach ($keyTypes as $type) {
                    switch ($type) {
                        case "string":
                            // Interpret PHP array map's as JSON objects.
                            $keySchemas[] = new Collection\ObjectMap(stdClass::class, $annotations);
                            break;

                        case "":
                            // Assume int index if not specified.
                        case "int":
                        case "integer":
                            $keySchemas[] = new Collection\ArrayList($match[1], $annotations);
                            break;

                        default:
                            throw new Exception\MalformedAnnotation("Arrays may only have keys of type string or int!");
                    }
                }

                if (count($keySchemas) == 1) {
                    $schema = $keySchemas[0];
                } else if (count($keySchemas) > 1) {
                    $schema = new Collection\ObjectMap(stdClass::class);
                    $schema->anyOf = $keySchemas;
                } else {

                }

                static::$definitions[$match[1]] = null;


                break;

            default:
                static::$definitions[$class] = null;
                $schema = new Collection\ObjectMap($class, $annotations);
        }

        /** @var AbstractSchema $schema */
        return $schema;
    }
}
