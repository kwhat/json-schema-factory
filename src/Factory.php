<?php

namespace Bildr\Json\Schema;

use Bildr\PoPoGadget\Exceptions;

class Factory
{
    /**
     * @param string $class
     * @param string|null $title
     * @param string|null $description
     * @throws Exceptions\InvalidClassNameException
     * @return ArrayType|ObjectType
     */
    public static function create($class, $title = null, $description = null)
    {
        if (!class_exists($class)) {
            throw new Exceptions\InvalidClassNameException("Class {$class} is not a valid class name.");
        }

        if (preg_match('/[\[](\s)*[\]]$/', $class)) {
            $returnSchema = new ArrayType($class);
        } else {
            $returnSchema = new ObjectType($class);
        }

        $returnSchema->setTitle($title);
        $returnSchema->setDescription($description);
        return $returnSchema;
    }
}
