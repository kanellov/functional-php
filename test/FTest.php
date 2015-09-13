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
        $functor = new F($callable);
        $this->assertSame($callable, $functor->extract());
    }

    public function testInvokeMethod()
    {
        $returnValue = 'executed';
        $callable    = function () use ($returnValue) {
            return $returnValue;
        };
        $functor = new F($callable);
        $this->assertSame($returnValue, $functor());
    }

    public function testMemoizeMethod()
    {
        $countCalls = 0;
        $callable   = function ($arg = null) use (&$countCalls) {
            ++$countCalls;

            return $arg;
        };
        $functor = new F($callable);
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

        $functor = new F($callable1);
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
        $functor = new F($callable);
        $this->assertEquals('arg is 5', $functor(5));
        $wrapped = $functor->wrap($wrapper);
        $this->assertEquals('The arg is 6!', $wrapped(6));
    }

    public function testCurryMethod()
    {
        $callable = function ($a, $b) {
            return $a + $b;
        };
        $functor = new F($callable);
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
        $functor = new F($callable);
        $curried = $functor->curryRight(5);
        $this->assertEquals(8, $curried(3));
        $this->setExpectedException('PHPUnit_Framework_Error');
        $curried();
    }
}
