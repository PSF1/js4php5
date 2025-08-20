<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\JS;
use js4php5\runtime\Runtime;
use js4php5\runtime\jsException;
use js4php5\runtime\jsObject;
use js4php5\runtime\Base;

final class RuntimeTryCatchTest extends TestCase
{
  protected function setUp(): void
  {
    // Reset Runtime and init
    $reset = \Closure::bind(function () {
      Runtime::$global = null;
      Runtime::$contexts = [];
      Runtime::$functions = [];
      Runtime::$zero = null;
      Runtime::$one = null;
      Runtime::$true = null;
      Runtime::$false = null;
      Runtime::$null = null;
      Runtime::$undefined = null;
      Runtime::$nan = null;
      Runtime::$infinity = null;
      Runtime::$exception = null;
      Runtime::$sortfn = null;
      Runtime::$proto_object = null;
      Runtime::$proto_function = null;
      Runtime::$proto_array = null;
      Runtime::$proto_string = null;
      Runtime::$proto_boolean = null;
      Runtime::$proto_number = null;
      Runtime::$proto_date = null;
      Runtime::$proto_regexp = null;
      Runtime::$proto_error = null;
      Runtime::$proto_evalerror = null;
      Runtime::$proto_rangeerror = null;
      Runtime::$proto_referenceerror = null;
      Runtime::$proto_syntaxerror = null;
      Runtime::$proto_typeerror = null;
      Runtime::$proto_urierror = null;
      Runtime::$startExtender = null;
      Runtime::$idcache = [];
    }, null, Runtime::class);
    $reset();

    JS::init();
  }

  public function testTryCatchHandlesThrownJsExceptionAndReturnsCatchValue(): void
  {
    // Arrange: simulate a thrown JS exception carrying a Base value
    Runtime::$exception = new jsException(Runtime::js_str('Boom'));

    // Catch closure should read the bound identifier from scope and return a value
    $catch = function () {
      // Access the identifier "e" bound by trycatch via scope
      $val = Runtime::id('e')->getValue(); // Base
      // Assert inside: ensure we got the carried value
      TestCase::assertSame('Boom', $val->toStr()->value);
      // Return a Base to become the overall result
      return Runtime::js_str('handled');
    };

    // Finally returns null (no override)
    $finally = function () {
      return null;
    };

    // Act: $expr must be null to avoid the internal warning branch
    $result = Runtime::trycatch(null, $catch, $finally, 'e');

    // Assert: result is the catch return; exception cleared
    $this->assertInstanceOf(Base::class, $result);
    $this->assertSame('handled', $result->toStr()->value);
    $this->assertNull(Runtime::$exception);
  }

  public function testTryCatchFinallyOverridesResult(): void
  {
    // Arrange: thrown exception with value 123
    Runtime::$exception = new jsException(Runtime::js_int(123));

    // Catch does nothing (returns null)
    $catch = function () {
      return null;
    };

    // Finally returns a Base that must override the result
    $finally = function () {
      return Runtime::js_str('from finally');
    };

    // Act
    $result = Runtime::trycatch(null, $catch, $finally, 'err');

    // Assert
    $this->assertSame('from finally', $result->toStr()->value);
    $this->assertNull(Runtime::$exception);
  }

  public function testTryCatchWithoutExceptionSkipsCatchAndPreservesExpr(): void
  {
    // Arrange: no exception set
    Runtime::$exception = null;

    $catchCalled = false;
    $catch = function () use (&$catchCalled) {
      $catchCalled = true;
      return Runtime::js_str('should not run');
    };

    $finallyCalled = false;
    $finally = function () use (&$finallyCalled) {
      $finallyCalled = true;
      return null; // do not override expr
    };

    $expr = Runtime::js_str('ok');

    // Act
    $result = Runtime::trycatch($expr, $catch, $finally, 'x');

    // Assert
    $this->assertFalse($catchCalled, 'Catch must not be called when no exception is pending');
    $this->assertTrue($finallyCalled, 'Finally must be called even without exception');
    $this->assertSame('ok', $result->toStr()->value);
  }
}
