<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\JS;
use js4php5\runtime\Base;
use js4php5\runtime\Runtime;
use js4php5\runtime\jsNumber;
use js4php5\runtime\jsString;
use js4php5\runtime\jsObject;

final class BaseConversionsTest extends TestCase
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

  public function testToBoolean(): void
  {
    $this->assertFalse(Runtime::$undefined->toBoolean()->value);
    $this->assertFalse(Runtime::$null->toBoolean()->value);
    $this->assertTrue((new jsObject())->toBoolean()->value);
    $this->assertTrue(Runtime::$true->toBoolean()->value);
    $this->assertFalse(Runtime::js_int(0)->toBoolean()->value);
    $this->assertTrue(Runtime::js_int(3.14)->toBoolean()->value);

    $nan = Runtime::$nan; // NaN should be falsy
    $this->assertFalse($nan->toBoolean()->value);

    $empty = Runtime::js_str('');
    $this->assertFalse($empty->toBoolean()->value);
    $nonEmpty = Runtime::js_str('x');
    $this->assertTrue($nonEmpty->toBoolean()->value);
  }

  public function testToNumberFromString(): void
  {
    $n = Runtime::js_str('42')->toNumber();
    $this->assertSame(42.0, $n->value);

    $nan = Runtime::js_str('foo')->toNumber();
    $this->assertTrue(is_nan($nan->value));
  }

  public function testToInt32AndToUInt32(): void
  {
    $v = new Base(Base::NUMBER, -1.7);
    $i32 = $v->toInt32();
    $this->assertSame(-1.0, $i32->toNumber()->value);

    $u32 = $v->toUInt32();
    $this->assertSame(4294967295.0, $u32->toNumber()->value);

    $large = new Base(Base::NUMBER, 4294967297.0); // 2^32 + 1
    $u32b = $large->toUInt32();
    $this->assertSame(1.0, $u32b->toNumber()->value);
  }

  public function testToUInt16(): void
  {
    $neg = new Base(Base::NUMBER, -1.0);
    $u16 = $neg->toUInt16();
    $this->assertSame(65535.0, $u16->toNumber()->value);

    $wrap = new Base(Base::NUMBER, 65537.0);
    $u16b = $wrap->toUInt16();
    $this->assertSame(1.0, $u16b->toNumber()->value);
  }

  public function testToStrForNumbers(): void
  {
    $this->assertSame('NaN', Runtime::$nan->toStr()->value);
    $this->assertSame('0', Runtime::js_int(0)->toStr()->value);
    $this->assertSame('-3.5', (new Base(Base::NUMBER, -3.5))->toStr()->value);
    $this->assertSame('Infinity', Runtime::$infinity->toStr()->value);
  }

  public function testToObjectThrowsOnNullOrUndefined(): void
  {
    $this->expectException(\js4php5\runtime\jsException::class);
    Runtime::$null->toObject();
  }

  public function testToObjectReturnsWrapperObjects(): void
  {
    $nObj = Runtime::js_int(7)->toObject();
    $this->assertInstanceOf(jsNumber::class, $nObj);

    $sObj = Runtime::js_str('x')->toObject();
    $this->assertInstanceOf(jsString::class, $sObj);
  }
}
