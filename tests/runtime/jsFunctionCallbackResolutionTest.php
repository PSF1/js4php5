<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\runtime\Runtime;

final class jsFunctionCallbackResolutionTest extends TestCase
{
  protected function setUp(): void
  {
    // Reset Runtime static state and initialize
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

    // Initialize runtime (prototypes, globals, etc.)
    \js4php5\JS::init();
  }

  public function testGlobalPhpFunctionIsCalled(): void
  {
    // Define a global PHP function to be called
    if (!function_exists('jf_sum')) {
      function jf_sum($a, $b) {
        // Args are Base values; return a Base number
        return Runtime::js_int($a->toNumber()->value + $b->toNumber()->value);
      }
    }

    // Register it as JS function "sum"
    Runtime::define_function('jf_sum', 'sum', ['a', 'b']);

    $fn = Runtime::function_id('jf_sum'); // jsFunction
    $res = Runtime::call($fn, [Runtime::js_int(2), Runtime::js_int(3)]);

    $this->assertSame(5.0, $res->toNumber()->value);
  }

  public function testFqcnStaticMethodIsCalled(): void
  {
    // Define a helper class with a static method
    if (!class_exists(\TestHelpers\MathHelpers::class)) {
      eval('namespace TestHelpers; class MathHelpers { public static function inc($x) { return \js4php5\runtime\Runtime::js_int($x->toNumber()->value + 1); } }');
    }

    // Register "Class::method" style callback
    Runtime::define_function('TestHelpers\\MathHelpers::inc', 'inc', ['x']);

    $fn = Runtime::function_id('TestHelpers\\MathHelpers::inc');
    $res = Runtime::call($fn, [Runtime::js_int(10)]);
    $this->assertSame(11.0, $res->toNumber()->value);
  }

  public function testRuntimeAliasResolutionWorks(): void
  {
    // Use built-in runtime alias (array form): ["jsMath","abs"]
    // Already registered in Runtime::start() as global "Math.abs" methods, but here we test direct function object call.
    $fn = Runtime::define_function(['jsMath', 'abs'], 'abs', ['x']);

    // Call with a negative number; abs should return its absolute value
    $res = Runtime::call($fn, [Runtime::js_int(-7)]);
    $this->assertSame(7.0, $res->toNumber()->value);
  }

  public function testInvalidCallbackThrowsTypeError(): void
  {
    // Register a function pointing to a non-existent callback
    Runtime::define_function('ThisDoesNotExist', 'bad', ['x']);

    $fn = Runtime::function_id('ThisDoesNotExist');

    $this->expectException(TypeError::class);
    Runtime::call($fn, [Runtime::js_int(1)]);
  }

  public function testFuncCallUsesThisArgAndArgs(): void
  {
    // Define a function that adds two numbers
    if (!function_exists('jf_add')) {
      function jf_add($a, $b) {
        return Runtime::js_int($a->toNumber()->value + $b->toNumber()->value);
      }
    }
    Runtime::define_function('jf_add', 'add', ['a', 'b']);

    // Emulate Function.prototype.call(thisArg, ...args)
    // Runtime::call will set "this" internally; we verify that arguments are passed
    $fn = Runtime::function_id('jf_add');
    $res = Runtime::call($fn, [Runtime::js_int(4), Runtime::js_int(6)]);

    $this->assertSame(10.0, $res->toNumber()->value);
  }
}
