<?php

namespace JsonSchema;

class Factory
{
    /**
     * @param string $class
     * @param string[] $annotations
     *
     * @return SchemaInterface
     * @throws Exception\ClassNotFound
     */
    public static function create($class, array $annotations = [])
    {
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

            // Match primitive and object array notation.
            case preg_match('/(.+)\[\s?\]$/', $class) == 1:
                $schema = new Collection\ArrayList($class, $annotations);
                break;

            default:
                $schema = new Collection\ObjectMap($class, $annotations);
        }

        /** @var SchemaInterface $schema */
        return $schema;
    }
}
