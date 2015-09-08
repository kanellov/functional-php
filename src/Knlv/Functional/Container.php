<?php

namespace Knlv\Functional;

use InvalidArgumentException;
use RuntimeException;

class Container
{
    protected $factories = [];

    public function __construct($factories = [])
    {
        $names = array_keys($factories);
        if ($names !== $factories) {
            throw new InvalidArgumentException('Expected an associative array');
        }

        $this->factories = array_map(function ($name, callable $fn) {
            $this->{$name} = $fn;
        }, $names, $factories);
    }

    public function __set($name, callable $fn)
    {
        if (!array_key_exists($name, $this->factories)) {
            $this->factories[$name] = $fn;
        }
        throw new RuntimeException(
            sprintf('Service %s not found', $name)
        );
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->factories)) {
            return $this->factories[$name]($this);
        }

        return null;
    }
}

