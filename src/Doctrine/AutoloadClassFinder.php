<?php

namespace JsonSchema\Doctrine;

use Doctrine\Common\Reflection;
use ReflectionClass;

class AutoloadFinder implements Reflection\ClassFinderInterface
{
    /**
     * @param string $class
     *
     * @return string|null $result
     */
    public function findFile($class)
    {
        $result = null;
        if (class_exists($class, true)) {
            $temp = new ReflectionClass($class);
            $result = $temp->getFileName();
        }

        return $result;
    }
}
