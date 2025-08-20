<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\JS;
use js4php5\runtime\Runtime;
use js4php5\runtime\jsObject;

final class JSConvertReturnValueTest extends TestCase
{
  protected function setUp(): void
  {
    // Init runtime (needed for Null/Undefined constants)
    $reset = \Closure::bind(function () {
      Runtime::$global = null;
      Runtime::$contexts = [];
      Runtime::$functions = [];
      Runtime::$startExtender = null;
      Runtime::$idcache = [];
    }, null, Runtime::class);
    $reset();
    JS::init();
  }

  public function testConvertPrimitives(): void
  {
    $this->assertNull(JS::convertReturnValue(Runtime::$undefined));
    $this->assertNull(JS::convertReturnValue(Runtime::$null));
    $this->assertSame('abc', JS::convertReturnValue(Runtime::js_str('abc')));
    $this->assertSame(42.0, JS::convertReturnValue(Runtime::js_int(42)));
    $this->assertTrue(JS::convertReturnValue(Runtime::$true));
    $this->assertFalse(JS::convertReturnValue(Runtime::$false));
  }

  public function testConvertObjectToPhpArray(): void
  {
    $o = new jsObject();
    $o->put('a', Runtime::js_int(1));
    $o->put('b', Runtime::js_str('x'));
    $o->put('c', Runtime::$true);

    $arr = JS::convertReturnValue($o);

    $this->assertIsArray($arr);
    $this->assertSame(1.0, $arr['a']);
    $this->assertSame('x', $arr['b']);
    $this->assertTrue($arr['c']);
  }
}
