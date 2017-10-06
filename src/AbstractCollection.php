<?php

namespace JsonSchema;

abstract class AbstractCollection extends AbstractSchema
{
    /**
     * @param string $type
     *
     * @return string|false $fullNamespace
     */
    protected function getFullNamespace($type)
    {
        $fullNamespace = false;

        $isArray = false;
        if (substr($type, -2) == "[]") {
            $isArray = true;
            $type = substr($type, 0, -2);
        }

        if (class_exists($type)) {
            // Check for fully qualified name.
            $fullNamespace = $type;
        } else if (class_exists($this->namespace . "\\" . $type)) {
            // Check for relative namespace.
            $fullNamespace = $this->namespace . "\\" . $type;
        } else {
            // Check for use statements.
            foreach($this->imports as $use) {
                if (preg_match('/(.+)\s+as\s+' . preg_quote($type, "\\") . '$/', $use, $match)) {
                    // Check for use alias.
                    if (class_exists($match[1])) {
                        $fullNamespace = $match[1];
                        break;
                    }
                } else if (preg_match('/(.*)' . preg_quote($type, "\\") . '$/', $use, $match)) {
                    // Check for use ending with.
                    if (class_exists($match[0])) {
                        $fullNamespace = $match[0];
                        break;
                    }
                } else if (class_exists("{$use}\\{$type}")) {
                    // Check for use relative namespace.
                    $fullNamespace = "{$use}\\{$type}";
                    break;
                } else {
                    // Check for use extension namespace.
                    $separator = strrpos($use , "\\");
                    if ($separator !== false) {
                        $class = substr($use, 0, $separator + 1) . $type;
                        if (class_exists($class)) {
                            $fullNamespace = $class;
                            break;
                        }
                    }
                }
            }
        }

        if ($fullNamespace && $isArray) {
            $fullNamespace .= "[]";
        }

        return $fullNamespace;
    }

}
