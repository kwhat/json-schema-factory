<?php

namespace Json\Schema\Doctrine;

use Doctrine\Common\Reflection;
use ReflectionClass;

class Psr4FileFinder implements Reflection\ClassFinderInterface
{
    /**
     * @param string $class
     * @return null|string $result
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
