<?php

namespace JsonSchema;

class Factory
{
    /**
     * @param string $class
     * @param string|null $title
     * @param string|null $description
     * @throws Exception\InvalidClassName
     * @return ArrayType|ObjectType
     */
    public static function create($class, $title = null, $description = null)
    {
        if (! class_exists($class)) {
            throw new Exception\InvalidClassName("Class {$class} is not a valid class name.");
        }

        if (preg_match('/[\[](\s)*[\]]$/', $class) !== false) {
            $schema = new ArrayType($class);
        } else {
            $schema = new ObjectType($class);
        }

        $schema->setTitle($title);
        $schema->setDescription($description);
        return $schema;
    }
}
