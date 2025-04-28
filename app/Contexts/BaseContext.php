<?php

namespace App\Contexts;

class BaseContext
{
    public array $properties;
    public bool $requiresAsync;

    public function __construct(array $properties = [], bool $requiresAsync = false)
    {
        $this->properties = $properties;
        $this->requiresAsync = $requiresAsync;
    }

    public function __get($name)
    {
        return data_get($this->properties, $name);
    }

    public function __call($name, $arguments)
    {
        if (strpos($name, 'get') === 0) {
            $property = lcfirst(substr($name, 3));
            return $this->properties[$property] ?? null;
        } elseif (strpos($name, 'set') === 0) {
            $property = lcfirst(substr($name, 3));
            $this->properties[$property] = $arguments[0];
            return;
        }

        throw new \BadMethodCallException("Method '{$name}' does not exist in the context.");
    }

    public function isEmpty(): bool
    {
        return empty($this->properties);
    }

    public function has(...$properties): bool
    {
        foreach ($properties as $property) {
            if (!array_key_exists($property, $this->properties)) {
                return false;
            }
        }
        return true;
    }

    public function setProperties(array $properties): void
    {
        foreach ($properties as $key => $value) {
            $this->properties[$key] = $value;
        }
    }
}
