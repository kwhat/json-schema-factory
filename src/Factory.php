<?php

namespace JsonSchema;

class Factory
{
    /**
     * @param string $class
     * @param string|null $title
     * @param string|null $description
     *
     * @return ArrayType|ObjectType
     * @throws Exception\InvalidClassName
     */
    public static function create($class, $title = null, $description = null)
    {
        if (preg_match('/[\[](\s)*[\]]$/', $class) !== false) {
            $schema = new Collection\ArrayList($class);
        } else {
            $schema = new Collection($class);
        }

        $schema->setTitle($title);
        $schema->setDescription($description);
        return $schema;
    }
}
