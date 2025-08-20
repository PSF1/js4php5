<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\JS;
use js4php5\runtime\Runtime;
use js4php5\runtime\jsAttribute;
use js4php5\runtime\jsObject;

final class jsAttributeTest extends TestCase
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

  public function testConstructorSetsBooleanFlags(): void
  {
    $val = Runtime::js_int(1);
    $attr = new jsAttribute($val, true, false, true);

    $this->assertSame($val, $attr->value);
    $this->assertTrue($attr->readonly);
    $this->assertFalse($attr->dontenum);
    $this->assertTrue($attr->dontdelete);
  }

  public function testJsObjectPutSetsFlagsFromOptions(): void
  {
    $obj = new jsObject();
    $obj->put('x', Runtime::js_int(123), ['readonly', 'dontenum']);

    $this->assertArrayHasKey('x', $obj->slots);
    $attr = $obj->slots['x'];

    $this->assertInstanceOf(jsAttribute::class, $attr);
    $this->assertTrue($attr->readonly);
    $this->assertTrue($attr->dontenum);
    $this->assertFalse($attr->dontdelete);
  }
}
