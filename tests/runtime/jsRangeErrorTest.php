<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\JS;
use js4php5\runtime\Runtime;
use js4php5\runtime\jsRangeError;
use js4php5\runtime\jsObject;
use js4php5\runtime\Base;
use js4php5\runtime\jsRef;

final class jsRangeErrorTest extends TestCase
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

  // Helper to call a method with proper "this" binding
  private function callMethod(object $obj, string $method, array $args = [])
  {
    $ref = new jsRef($obj, $method);
    return Runtime::call($ref, $args);
  }

  public function testConstructorSetsNameMessageAndToString(): void
  {
    $e = new jsRangeError('Out of range!');
    $this->assertSame('RangeError', $e->get('name')->toStr()->value);
    $this->assertSame('Out of range!', $e->get('message')->toStr()->value);

    $s = $this->callMethod($e, 'toString', []);
    $this->assertSame(Base::STRING, $s->type);
    $this->assertSame('RangeError: Out of range!', $s->value);
  }

  public function testGlobalRangeErrorFunctionCreatesRangeError(): void
  {
    // Global constructor registered in Runtime::start(): "RangeError"
    $fnRef = Runtime::id('RangeError');
    $e = Runtime::call($fnRef, [Runtime::js_str('Oops')]);

    $this->assertInstanceOf(jsRangeError::class, $e);
    $this->assertSame('Oops', $e->get('message')->toStr()->value);
    $this->assertSame('RangeError', $e->get('name')->toStr()->value);
  }

  public function testToStringThrowsOnWrongThis(): void
  {
    $notError = new jsObject();
    Runtime::push_context($notError);
    try {
      $this->expectException(\js4php5\runtime\jsException::class);
      jsRangeError::toString(); // inherited from jsError, validates "this"
    } finally {
      Runtime::pop_context();
    }
  }
}
