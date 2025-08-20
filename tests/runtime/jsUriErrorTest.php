<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\JS;
use js4php5\runtime\Runtime;
use js4php5\runtime\jsUriError;
use js4php5\runtime\jsObject;
use js4php5\runtime\Base;
use js4php5\runtime\jsRef;

final class jsUriErrorTest extends TestCase
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
  private function call(object $obj, string $method, array $args = []): Base
  {
    $ref = new jsRef($obj, $method);
    return Runtime::call($ref, $args);
  }

  public function testConstructorSetsNameMessageAndToString(): void
  {
    $e = new jsUriError('Bad URI');
    $this->assertSame('URIError', $e->get('name')->toStr()->value);
    $this->assertSame('Bad URI', $e->get('message')->toStr()->value);

    $s = $this->call($e, 'toString', []);
    $this->assertSame(Base::STRING, $s->type);
    $this->assertSame('URIError: Bad URI', $s->value);
  }

  public function testGlobalUriErrorFunctionCreatesUriError(): void
  {
    // Global constructor registered in Runtime::start(): "URIError"
    $fnRef = Runtime::id('URIError');
    $e = Runtime::call($fnRef, [Runtime::js_str('Oops')]);

    $this->assertInstanceOf(jsUriError::class, $e);
    $this->assertSame('Oops', $e->get('message')->toStr()->value);
    $this->assertSame('URIError', $e->get('name')->toStr()->value);
  }

  public function testToStringThrowsOnWrongThis(): void
  {
    $notError = new jsObject();
    Runtime::push_context($notError);
    try {
      $this->expectException(\js4php5\runtime\jsException::class);
      // toString is inherited from jsError and validates "this"
      jsUriError::toString();
    } finally {
      Runtime::pop_context();
    }
  }
}
