<?php

namespace KnlvTest\Functional;

use Knlv\Functional\F;

class FTest extends \PHPUnit_Framework_TestCase
{
    public function testContructorSetsCallable()
    {
        $callable = function () {};
        $functor = new F($callable);
        $this->assertAttributeSame($callable, 'fn', $functor);
    }

    public function testCreateMethod()
    {
        $callable = function () {};
        $functor = F::create($callable);
        $this->assertAttributeSame($callable, 'fn', $functor);
    }

    public function testExtractMethodReturnWrappedCallable()
    {
        $callable = function () {};
        $functor = F::create($callable);
        $this->assertSame($callable, $functor->extract());
    }

    public function testInvokeMethod()
    {
        $returnValue = 'executed';
        $callable    = function () use ($returnValue) {
            return $returnValue;
        };
        $functor = F::create($callable);
        $this->assertSame($returnValue, $functor());
    }

    public function testMemoizeMethod()
    {
        $countCalls = 0;
        $callable   = function ($arg = null) use (&$countCalls) {
            ++$countCalls;

            return $arg;
        };
        $functor = F::create($callable);
        $this->assertEquals('a', $functor('a'));
        $this->assertEquals(1, $countCalls);

        $memoized = $functor->memoize();
        $this->assertInstanceOf('\Knlv\Functional\F', $memoized);
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

        $functor = F::create($callable1);
        $this->assertEquals(6, $functor(5));

        $composed = $functor->compose($callable2);
        $this->assertInstanceOf('\Knlv\Functional\F', $composed);
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
        $functor = F::create($callable);
        $this->assertEquals('arg is 5', $functor(5));
        $wrapped = $functor->wrap($wrapper);
        $this->assertInstanceOf('\Knlv\Functional\F', $wrapped);
        $this->assertEquals('The arg is 6!', $wrapped(6));
    }

    public function testCurryMethod()
    {
        $callable = function ($a, $b) {
            return $a / $b;
        };
        $functor = F::create($callable);
        $curried = $functor->curry(10);
        $this->assertInstanceOf('\Knlv\Functional\F', $curried);
        $this->assertEquals(5, $curried(2));
        $this->setExpectedException('\Exception');
        $curried();
    }

    public function testCurryRightMethod()
    {
        $callable = function ($a, $b) {
            return $a % $b;
        };
        $functor = F::create($callable);
        $curried = $functor->curryRight(2);
        $this->assertInstanceOf('\Knlv\Functional\F', $curried);
        $this->assertEquals(0, $curried(4));
        $this->assertEquals(1, $curried(3));
        $this->setExpectedException('PHPUnit_Framework_Error');
        $curried();
    }

    public function testTrampolineMethod()
    {
        $callable = function ($n) use (&$callable) {
            if (0 === $n) {
                return 0;
            }

            return function () use ($n, &$callable) {
                return call_user_func($callable, $n - 1);
            };
        };
        $functor    = F::create($callable);
        $trampoline = $functor->trampoline();
        $this->assertInstanceOf('\Knlv\Functional\F', $trampoline);
        $result = $trampoline(1000);
        $this->assertSame(0, $result);
    }
}
