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
            $key = md5(serialize($args));
            if (!array_key_exists($key, $memoized)) {
                $memoized[$key] = $this($args);
            }

            return $memoized[$key];
        });
    }


    public function curry()
    {
        
    }

    public function curryRight()
    {

    }
    
    public function compose(callable $fn)
    {
        
    }
    
    public function fn()
    {
        return $this->fn;
    }
}

