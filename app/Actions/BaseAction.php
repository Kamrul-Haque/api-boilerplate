<?php

namespace App\Actions;

use Exception;
use ReflectionClass;
use ReflectionMethod;

abstract class BaseAction
{
    /**
     * Blocks creating constructor on child classes and initiates public method validation while creating child object
     *
     * @throws Exception
     */
    final public function __construct()
    {
        $this->validatePublicMethods();
    }

    /**
     * Validates public methods on child classes and if found throws error
     *
     * @throws Exception
     */
    private function validatePublicMethods(): void
    {
        $reflection = new ReflectionClass($this);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        if (! method_exists($this, 'handle')) {
            throw new Exception(get_class($this).' must implement handle() method.');
        }

        foreach ($methods as $method) {
            if ($method->getName() !== 'handle' && $method->class === $reflection->getName()) {
                throw new Exception("public method '{$method->getName()}' is not allowed in class '{$method->class}'.");
            }
        }
    }
}
