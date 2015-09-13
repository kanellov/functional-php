<?php

namespace Knlv\Functional;

class Functor
{
    protected $fn;

    public function __construct(callable $fn)
    {
        $this->fn = $fn;
    }

    public function __invoke()
    {
        return call_user_func_array($this->fn, func_get_args());
    }

    public function memoize()
    {
        return new static(function () {
            static $memoized = [];
            $args = func_get_args();
            $key = md5(var_export($args, true));
            if (!array_key_exists($key, $memoized)) {
                $memoized[$key] = call_user_func_array($this, $args);
            }

            return $memoized[$key];
        });
    }

    public function compose(callable $fn)
    {
        return new static(function () use (&$fn) {
            $args = func_get_args();

            return $fn(call_user_func_array($this, $args));
        });
    }

    public function wrap(callable $fn)
    {
        return new static(function () use (&$fn) {
            $args = array_merge([$this], func_get_args());

            return call_user_func_array($fn, $args);
        });
    }

    public function curry()
    {
        $args = func_get_args();

        return new static(function () use ($args) {
            return call_user_func_array($this, array_merge(
                $args,
                func_get_args()
            ));
        });
    }

    public function curryRight()
    {
        $args = func_get_args();

        return new static(function () use ($args) {
            return call_user_func_array($this, array_merge(
                func_get_args(),
                $args
            ));
        });
    }

    public function fn()
    {
        return $this->fn;
    }
}
