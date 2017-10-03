<?php

namespace JsonSchema;

use stdClass;

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
                if (isset(static::$definitions[$class])) {
                    $schema = array(
                        "\$ref" => "#/definitions/" . str_replace("\\", "/", $class)
                    );
                } else {
                    switch ($match[2]) {
                        case "":
                            // Assume int index if not specified.
                        case "int":
                        case "integer":
                            $schema = new Collection\ArrayList($match[1], $annotations);
                            break;

                        case "string":
                            // Add the map type to the catch all pattern property.
                            $annotations[] = "@patternProperties {$match[1]} .*";

                            // Interpret PHP array map's as JSON objects.
                            $schema = new Collection\ObjectMap(stdClass::class, $annotations);
                            break;

                        case "string|int":
                        case "int|string":
                            // Add the map type to the catch all pattern property.
                            $annotations[] = "@patternProperties {$match[1]} .*";

                            $schema = new Condition\OneOf(array(
                                new Collection\ArrayList($match[1], $annotations),

                                // Interpret PHP array map's as JSON objects.
                                new Collection\ObjectMap(stdClass::class, $annotations)
                            ));
                            break;

                        default:
                            throw new Exception\MalformedAnnotation("Arrays may only have keys of type string or int!");
                    }
                }

                static::$definitions[$class] = null;


                break;

            default:
                static::$definitions[$class] = null;
                $schema = new Collection\ObjectMap($class, $annotations);
        }

        /** @var AbstractSchema $schema */
        return $schema;
    }


}
