<?php

namespace KnlvTest\Functional;

use Knlv\Functional\Functor;

class FunctorTest extends \PHPUnit_Framework_TestCase
{
    public function testContructorSetsCallable()
    {
        $callable = function () {};
        $functor = new Functor($callable);
        $this->assertAttributeSame($callable, 'fn', $functor);
    }


    public function testFnMethodReturnWrappedCallable()
    {
        $callable = function () {};
        $functor = new Functor($callable);
        $this->assertSame($callable, $functor->fn());
    }


    public function testInvokeMethod()
    {
        $returnValue = 'executed';
        $callable    = function () use ($returnValue) {
            return $returnValue;
        };
        $functor = new Functor($callable);
        $this->assertSame($returnValue, $functor());
    }


    public function testMemoizeMethod()
    {
        $countCalls = 0;
        $callable   = function ($arg = null) use (&$countCalls) {
            ++$countCalls;

            return $arg;
        };
        $functor = new Functor($callable);
        $this->assertEquals('a', $functor('a'));
        $this->assertEquals(1, $countCalls);

        $memoized = $functor->memoize();
        $this->assertEquals('b', $memoized('b'));
        $this->assertEquals('b', $memoized('b'));
        $this->assertEquals(2, $countCalls);
        $this->assertNull($memoized());
        $this->assertNull($memoized());
        $this->assertEquals(3, $countCalls);
    }


    public function testComposeMethod()
    {
        $callable1 = function ($a) {
            return $a + 1;
        };
        $callable2 = function ($b) {
            return $b * $b;
        };

        $functor = new Functor($callable1);
        $this->assertEquals(6, $functor(5));

        $composed = $functor->compose($callable2);
        $this->assertEquals(49, $composed(6));
    }


    public function testWrapMethod()
    {
        $callable = function ($a) {
            return 'arg is ' . (string) $a;
        };

        $wrapper = function ($fn, $b) {
            return 'The ' . $fn($b) . '!';
        };
        $functor = new Functor($callable);
        $this->assertEquals('arg is 5', $functor(5));
        $wrapped = $functor->wrap($wrapper);
        $this->assertEquals('The arg is 6!', $wrapped(6));
    }

    public function testCurryMethod()
    {
        $callable = function ($a, $b) {
            return $a + $b;
        };
        $functor = new Functor($callable);
        $curried = $functor->curry(5);
        $this->assertEquals(8, $curried(3));
        $this->setExpectedException('PHPUnit_Framework_Error');
        $curried();
    }

    public function testCurryRightMethod()
    {
        $callable = function ($a, $b) {
            return $a + $b;
        };
        $functor = new Functor($callable);
        $curried = $functor->curryRight(5);
        $this->assertEquals(8, $curried(3));
        $this->setExpectedException('PHPUnit_Framework_Error');
        $curried();
    }
}
