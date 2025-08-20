<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\JS;
use js4php5\runtime\Runtime;
use js4php5\runtime\jsRef;
use js4php5\runtime\jsObject;
use js4php5\runtime\jsArray;
use js4php5\runtime\Base;

final class jsRefTest extends TestCase
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

  public function testGetValueAndPutValueRet1AndRet2(): void
  {
    $obj = new jsObject();
    $obj->put('x', Runtime::js_int(1));

    $ref = new jsRef($obj, 'x');

    // getValue returns the current value
    $val = $ref->getValue();
    $this->assertSame(1.0, $val->toNumber()->value);

    // putValue with ret=1 returns the written value
    $ret1 = $ref->putValue(Runtime::js_int(5), 1);
    $this->assertSame(5.0, $ret1->toNumber()->value);
    $this->assertSame(5.0, $obj->get('x')->toNumber()->value);

    // putValue with ret=2 returns the previous value
    $ret2 = $ref->putValue(Runtime::js_int(7), 2);
    $this->assertSame(5.0, $ret2->toNumber()->value);
    $this->assertSame(7.0, $obj->get('x')->toNumber()->value);
  }

  public function testPutValueRet0ReturnsNullAndUpdates(): void
  {
    $obj = new jsObject();
    $obj->put('y', Runtime::js_int(10));

    $ref = new jsRef($obj, 'y');

    $ret0 = $ref->putValue(Runtime::js_int(11), 0);
    $this->assertNull($ret0);
    $this->assertSame(11.0, $obj->get('y')->toNumber()->value);
  }

  public function testNumericPropertyOnArray(): void
  {
    $arr = new jsArray();
    // Using jsRef with numeric index
    $ref0 = new jsRef($arr, 0);
    $ref0->putValue(Runtime::js_str('a'));

    $ref1 = new jsRef($arr, 1);
    $ref1->putValue(Runtime::js_str('b'));

    $this->assertSame('a', $ref0->getValue()->toStr()->value);
    $this->assertSame('b', $ref1->getValue()->toStr()->value);

    // Length should reflect 2 elements
    $this->assertSame(2.0, $arr->get('length')->toNumber()->value);
  }
}
