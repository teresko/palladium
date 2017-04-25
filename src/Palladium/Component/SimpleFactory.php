<?php

namespace Palladium\Component;

use RuntimeException;

class SimpleFactory
{

    /**
     * Methode for creating new instance of a given class
     *
     * @param string $name Fully qualified class name
     *
     * @throws RuntimeException if class can't be found
     *
     */
    public function create(string $className)
    {
        if (!class_exists($className)) {
            throw new RuntimeException("Mapper not found. Attempted to load '{$className}'.");
        }

        return new $className;
    }
}
