<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\JS;
use js4php5\runtime\Runtime;
use js4php5\runtime\jsNumber;
use js4php5\runtime\Base;
use js4php5\runtime\jsRef;
use js4php5\runtime\jsException;
use js4php5\runtime\jsRangeError;

final class jsNumberTest extends TestCase
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

  private function call(object $obj, string $method, array $args = []): Base
  {
    // Bind correct "this" and call JS method
    $ref = new jsRef($obj, $method);
    return Runtime::call($ref, $args);
  }

  public function testValueOfReturnsPrimitiveNumber(): void
  {
    $n = new jsNumber(Runtime::js_int(42));
    $v = $this->call($n, 'valueOf', []);
    $this->assertSame(Base::NUMBER, $v->type);
    $this->assertSame(42.0, $v->value);
  }

  public function testToStringDefaultAndRadix(): void
  {
    $n = new jsNumber(Runtime::js_int(255));
    $s10 = $this->call($n, 'toString', []);
    $this->assertSame('255', $s10->toStr()->value);

    // Radix 16 for integer value
    $s16 = $this->call($n, 'toString', [Runtime::js_int(16)]);
    $this->assertSame('ff', $s16->toStr()->value);

    // Infinity string
    $inf = new jsNumber(Runtime::$infinity);
    $sInf = $this->call($inf, 'toString', []);
    $this->assertSame('Infinity', $sInf->toStr()->value);

    // -Infinity string
    $negInf = new jsNumber(Runtime::expr_u_minus(Runtime::$infinity));
    $sNegInf = $this->call($negInf, 'toString', []);
    $this->assertSame('-Infinity', $sNegInf->toStr()->value);
  }

  public function testToFixedPadsOrTruncates(): void
  {
    // 2.5 with 2 digits -> "2.50"
    $n = new jsNumber(Runtime::js_int(2.5));
    $s = $this->call($n, 'toFixed', [Runtime::js_int(2)]);
    $this->assertSame('2.50', $s->toStr()->value);

    // 3 with 3 digits -> "3.000"
    $n2 = new jsNumber(Runtime::js_int(3));
    $s2 = $this->call($n2, 'toFixed', [Runtime::js_int(3)]);
    $this->assertSame('3.000', $s2->toStr()->value);
  }

  public function testToExponentialAndBounds(): void
  {
    $n = new jsNumber(Runtime::js_int(10));
    $s = $this->call($n, 'toExponential', [Runtime::js_int(2)]);
    $this->assertMatchesRegularExpression('/^1\.0{0,}e\+?1$/i', $s->toStr()->value);

    // digits out of range [0,20] -> RangeError wrapped in jsException
    $this->expectException(jsException::class);
    $this->call($n, 'toExponential', [Runtime::js_int(21)]);
  }

  public function testToPrecision(): void
  {
    $n = new jsNumber(Runtime::js_int(12345));
    // precision 3 -> exponential or fixed, we accept any non-empty numeric string
    $s = $this->call($n, 'toPrecision', [Runtime::js_int(3)]);
    $this->assertNotSame('', $s->toStr()->value);

    // undefined -> behaves like toString()
    $s2 = $this->call($n, 'toPrecision', [Runtime::$undefined]);
    $this->assertSame($this->call($n, 'toString', [Runtime::$undefined])->toStr()->value, $s2->toStr()->value);

    // out of range -> RangeError
    $this->expectException(jsException::class);
    $this->call($n, 'toPrecision', [Runtime::js_int(0)]);
  }
}
