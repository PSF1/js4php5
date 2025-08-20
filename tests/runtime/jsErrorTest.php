<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\JS;
use js4php5\runtime\Runtime;
use js4php5\runtime\jsError;
use js4php5\runtime\Base;
use js4php5\runtime\jsRef;
use js4php5\runtime\jsObject;

final class jsErrorTest extends TestCase
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

  private function callMethod(object $obj, string $method, array $args = [])
  {
    // Bind correct "this" by calling through a jsRef
    $ref = new jsRef($obj, $method);
    return Runtime::call($ref, $args);
  }

  public function testConstructorSetsNameAndMessageAndToString(): void
  {
    $e = new jsError('Error', null, 'Boom!');
    // Properties present
    $this->assertSame('Error', $e->get('name')->toStr()->value);
    $this->assertSame('Boom!', $e->get('message')->toStr()->value);

    // toString returns "Error: Boom!"
    $s = $this->callMethod($e, 'toString', []);
    $this->assertSame(Base::STRING, $s->type);
    $this->assertSame('Error: Boom!', $s->value);
  }

  public function testGlobalErrorFunctionCreatesErrorObject(): void
  {
    // Use the globally defined "Error" function (registered by Runtime::start)
    $fnRef = Runtime::id('Error'); // jsRef to global "Error"
    $e = Runtime::call($fnRef, [Runtime::js_str('Oops')]);

    $this->assertInstanceOf(jsError::class, $e);
    $this->assertSame('Oops', $e->get('message')->toStr()->value);
  }

  public function testToStringThrowsOnWrongThis(): void
  {
    $notError = new jsObject();

    // Force Runtime::this() to be $notError by pushing a context and calling the static method directly
    Runtime::push_context($notError);
    try {
      $this->expectException(\js4php5\runtime\jsException::class);
      jsError::toString();
    } finally {
      Runtime::pop_context();
    }
  }
}
