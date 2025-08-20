<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\JS;
use js4php5\runtime\Runtime;
use js4php5\runtime\Base;
use js4php5\runtime\jsRef;
use js4php5\runtime\jsObject;

final class jsMathTest extends TestCase
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

  private function mathCall(string $method, array $args = []): Base
  {
    // Retrieve the global Math object and build a ref to its method
    $math = Runtime::id('Math')->getValue(); // Base OBJECT
    $mathObj = $math->toObject();
    $ref = new jsRef($mathObj, $method);
    return Runtime::call($ref, $args);
  }

  public function testAbsAndBasicOps(): void
  {
    $r = $this->mathCall('abs', [Runtime::js_int(-3)]);
    $this->assertSame(3.0, $r->toNumber()->value);

    $r = $this->mathCall('ceil', [Runtime::js_int(2.1)]);
    $this->assertSame(3.0, $r->toNumber()->value);

    $r = $this->mathCall('floor', [Runtime::js_int(2.9)]);
    $this->assertSame(2.0, $r->toNumber()->value);

    $r = $this->mathCall('round', [Runtime::js_int(2.5)]);
    $this->assertSame(3.0, $r->toNumber()->value);

    $r = $this->mathCall('sqrt', [Runtime::js_int(4)]);
    $this->assertSame(2.0, $r->toNumber()->value);

    $r = $this->mathCall('pow', [Runtime::js_int(2), Runtime::js_int(3)]);
    $this->assertSame(8.0, $r->toNumber()->value);
  }

  public function testTrigAndLog(): void
  {
    $r = $this->mathCall('cos', [Runtime::js_int(0)]);
    $this->assertEqualsWithDelta(1.0, $r->toNumber()->value, 1e-12);

    $r = $this->mathCall('sin', [Runtime::js_int(0)]);
    $this->assertEqualsWithDelta(0.0, $r->toNumber()->value, 1e-12);

    $r = $this->mathCall('log', [Runtime::js_int(1)]);
    $this->assertEqualsWithDelta(0.0, $r->toNumber()->value, 1e-12);

    // acos(2) should be NaN
    $r = $this->mathCall('acos', [Runtime::js_int(2)]);
    $this->assertTrue(is_nan($r->toNumber()->value));
  }

  public function testMaxAndMin(): void
  {
    // min() with no args -> +Infinity
    $r = $this->mathCall('min', []);
    $this->assertTrue(is_infinite($r->toNumber()->value));
    $this->assertGreaterThan(0, $r->toNumber()->value);

    // max() with no args -> -Infinity
    $r = $this->mathCall('max', []);
    $this->assertTrue(is_infinite($r->toNumber()->value));
    $this->assertLessThan(0, $r->toNumber()->value);

    // max(1, NaN) -> NaN
    $r = $this->mathCall('max', [Runtime::js_int(1), Runtime::$nan]);
    $this->assertTrue(is_nan($r->toNumber()->value));

    // min(3, 1, 2) -> 1
    $r = $this->mathCall('min', [Runtime::js_int(3), Runtime::js_int(1), Runtime::js_int(2)]);
    $this->assertSame(1.0, $r->toNumber()->value);
  }

  public function testRandomReturnsInRange(): void
  {
    $values = [];
    for ($i = 0; $i < 10; $i++) {
      $r = $this->mathCall('random', []);
      $v = $r->toNumber()->value;
      $this->assertGreaterThanOrEqual(0.0, $v);
      $this->assertLessThan(1.0, $v, 'Math.random must be < 1');
      $values[] = $v;
    }
    // Should not be constant (very weak check)
    $this->assertGreaterThan(0, max($values) - min($values));
  }
}
