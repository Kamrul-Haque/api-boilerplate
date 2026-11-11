<?php

namespace App\Actions;

use Exception;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

abstract class BaseAction
{
    /**
     * Initiates public method validation while creating child object
     *
     * @throws Exception
     */
    public function __construct()
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
            throw new RuntimeException(get_class($this).' must implement handle() method.');
        }

        foreach ($methods as $method) {
            if ($method->getName() !== 'handle' && $method->getName() !== '__construct' && $method->class === $reflection->getName()) {
                throw new RuntimeException("public method '{$method->getName()}' is not allowed in class '{$method->class}'.");
            }
        }
    }
}
