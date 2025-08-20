<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\JS;
use js4php5\runtime\Runtime;
use js4php5\runtime\jsArray;
use js4php5\runtime\Base;
use js4php5\runtime\jsRef;

final class jsArrayTest extends TestCase
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

  private function callArrayMethod(jsArray $arr, string $method, array $args = [])
  {
    // Build a reference to arr[method] so Runtime::call binds "this" correctly
    $ref = new jsRef($arr, $method);
    return Runtime::call($ref, $args);
  }

  public function testConstructorAndLength(): void
  {
    $arr = new jsArray();
    $len = $arr->get('length')->toNumber()->value;
    $this->assertSame(0.0, $len);
  }

  public function testPushAndPop(): void
  {
    $arr = new jsArray();

    $newLen = $this->callArrayMethod($arr, 'push', [Runtime::js_str('a'), Runtime::js_str('b')]);
    $this->assertSame(2.0, $newLen->toNumber()->value);

    $popped = $this->callArrayMethod($arr, 'pop', []);
    $this->assertSame('b', $popped->toStr()->value);

    $len = $arr->get('length')->toNumber()->value;
    $this->assertSame(1.0, $len);
  }

  public function testShiftAndUnshift(): void
  {
    $arr = new jsArray();
    // Seed using internal helper
    $arr->_push(Runtime::js_str('x'));
    $arr->_push(Runtime::js_str('y'));

    $count = $this->callArrayMethod($arr, 'unshift', [Runtime::js_str('a'), Runtime::js_str('b')]);
    $this->assertSame(4.0, $count->toNumber()->value);

    $first = $this->callArrayMethod($arr, 'shift', []);
    $this->assertSame('a', $first->toStr()->value);

    $len = $arr->get('length')->toNumber()->value;
    $this->assertSame(3.0, $len);
  }

  public function testJoin(): void
  {
    $arr = new jsArray();
    $arr->_push(Runtime::js_str('a'));
    $arr->_push(Runtime::js_str('b'));
    $arr->_push(Runtime::js_str('c'));

    // Default separator ","
    $s1 = $this->callArrayMethod($arr, 'join', [Runtime::$undefined]);
    $this->assertSame('a,b,c', $s1->toStr()->value);

    // Custom separator
    $s2 = $this->callArrayMethod($arr, 'join', [Runtime::js_str('-')]);
    $this->assertSame('a-b-c', $s2->toStr()->value);
  }

  public function testSlice(): void
  {
    $arr = new jsArray();
    $arr->_push(Runtime::js_str('a'));
    $arr->_push(Runtime::js_str('b'));
    $arr->_push(Runtime::js_str('c'));
    $arr->_push(Runtime::js_str('d'));

    $slice = $this->callArrayMethod($arr, 'slice', [Runtime::js_int(1), Runtime::js_int(3)]);
    $this->assertInstanceOf(jsArray::class, $slice);

    $len = $slice->get('length')->toNumber()->value;
    $this->assertSame(2.0, $len);
    $this->assertSame('b', $slice->get(0)->toStr()->value);
    $this->assertSame('c', $slice->get(1)->toStr()->value);
  }

  public function testConcat(): void
  {
    $a1 = new jsArray();
    $a1->_push(Runtime::js_str('x'));
    $a1->_push(Runtime::js_str('y'));

    $a2 = new jsArray();
    $a2->_push(Runtime::js_str('z'));

    $res = $this->callArrayMethod($a1, 'concat', [$a2, Runtime::js_str('w')]);
    $this->assertInstanceOf(jsArray::class, $res);
    $this->assertSame(4.0, $res->get('length')->toNumber()->value);
    $this->assertSame('x', $res->get(0)->toStr()->value);
    $this->assertSame('y', $res->get(1)->toStr()->value);
    $this->assertSame('z', $res->get(2)->toStr()->value);
    $this->assertSame('w', $res->get(3)->toStr()->value);
  }

  public function testSpliceRemoveAndInsert(): void
  {
    $arr = new jsArray();
    $arr->_push(Runtime::js_str('a'));
    $arr->_push(Runtime::js_str('b'));
    $arr->_push(Runtime::js_str('c'));

    $removed = $this->callArrayMethod($arr, 'splice', [Runtime::js_int(1), Runtime::js_int(1), Runtime::js_str('X'), Runtime::js_str('Y')]);
    $this->assertInstanceOf(jsArray::class, $removed);
    $this->assertSame(1.0, $removed->get('length')->toNumber()->value);
    $this->assertSame('b', $removed->get(0)->toStr()->value);

    // Now array should be ['a','X','Y','c']
    $this->assertSame(4.0, $arr->get('length')->toNumber()->value);
    $this->assertSame('a', $arr->get(0)->toStr()->value);
    $this->assertSame('X', $arr->get(1)->toStr()->value);
    $this->assertSame('Y', $arr->get(2)->toStr()->value);
    $this->assertSame('c', $arr->get(3)->toStr()->value);
  }

  public function testDefaultValueToString(): void
  {
    $arr = new jsArray();
    $arr->_push(Runtime::js_str('hello'));
    $arr->_push(Runtime::js_str('world'));

    $s = $arr->defaultValue()->toStr()->value;
    $this->assertSame('hello,world', $s);
  }
}
