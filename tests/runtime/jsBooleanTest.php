<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\JS;
use js4php5\runtime\Runtime;
use js4php5\runtime\jsBoolean;
use js4php5\runtime\Base;
use js4php5\runtime\jsRef;

final class jsBooleanTest extends TestCase
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

  public function testConstructorDefaultIsFalse(): void
  {
    $obj = new jsBoolean(); // default uses undefined -> false
    $this->assertSame(Base::BOOLEAN, $obj->value->type);
    $this->assertFalse($obj->value->value);

    // Call toString with correct this binding
    $strBase = $this->callMethod($obj, 'toString', []);
    $this->assertSame('false', $strBase->toStr()->value);
  }

  public function testToStringAndValueOfForTrue(): void
  {
    $obj = new jsBoolean(Runtime::js_int(1)); // truthy -> true
    $this->assertTrue($obj->value->value);

    // toString -> "true"
    $s = $this->callMethod($obj, 'toString', []);
    $this->assertSame('true', $s->toStr()->value);

    // valueOf -> primitive boolean Base
    $v = $this->callMethod($obj, 'valueOf', []);
    $this->assertSame(Base::BOOLEAN, $v->type);
    $this->assertTrue($v->value);
  }

  public function testObjectFactoryReturnsPrimitiveOrWrapper(): void
  {
    // Not called as constructor: should return primitive boolean Base
    $prim = jsBoolean::object(Runtime::js_int(0));
    $this->assertInstanceOf(Base::class, $prim);
    $this->assertSame(Base::BOOLEAN, $prim->type);
    $this->assertFalse($prim->value);

    // Simulate "new Boolean(...)" by toggling the constructor flag
    \js4php5\runtime\jsFunction::$constructor = 1;
    try {
      $wrap = jsBoolean::object(Runtime::js_int(2));
      $this->assertInstanceOf(jsBoolean::class, $wrap);
      $this->assertTrue($wrap->value->value);
    } finally {
      \js4php5\runtime\jsFunction::$constructor = 0;
    }
  }
}
