<?php

namespace JsonSchema\Doctrine;

use Doctrine\Common\Reflection;
use ReflectionClass;

class AutoloadClassFinder implements Reflection\ClassFinderInterface
{
    /**
     * @param string $class
     *
     * @return string|null
     */
    public function findFile($class)
    {
        $file = null;
        if (class_exists($class, true)) {
            $reflectionClass = new ReflectionClass($class);
            $file = $reflectionClass->getFileName();
        }

        return $file;
    }
}
