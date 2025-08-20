<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\JS;
use js4php5\runtime\Runtime;
use js4php5\runtime\jsString;
use js4php5\runtime\jsArray;
use js4php5\runtime\Base;
use js4php5\runtime\jsRef;

final class jsStringTest extends TestCase
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
    $ref = new jsRef($obj, $method);
    return Runtime::call($ref, $args);
  }

  public function testConstructorAndLength(): void
  {
    $s = new jsString(Runtime::js_str('abc'));
    $this->assertSame('abc', $s->value->toStr()->value);
    $len = $s->get('length')->toNumber()->value;
    $this->assertSame(3.0, $len);
  }

  public function testCharAtAndCharCodeAt(): void
  {
    $s = new jsString(Runtime::js_str('ABC'));
    $this->assertSame('B', $this->call($s, 'charAt', [Runtime::js_int(1)])->toStr()->value);
    $this->assertSame(65.0, $this->call($s, 'charCodeAt', [Runtime::js_int(0)])->toNumber()->value);
    // Out of range
    $this->assertSame('', $this->call($s, 'charAt', [Runtime::js_int(5)])->toStr()->value);
  }

  public function testConcat(): void
  {
    $s = new jsString(Runtime::js_str('a'));
    $r = $this->call($s, 'concat', [Runtime::js_str('b'), Runtime::js_str('c')]);
    $this->assertSame('abc', $r->toStr()->value);
  }

  public function testIndexOfWithAndWithoutPos(): void
  {
    $s = new jsString(Runtime::js_str('banana'));
    // Without pos (undefined) should start at 0
    $r1 = $this->call($s, 'indexOf', [Runtime::js_str('na')]);
    $this->assertSame(2.0, $r1->toNumber()->value);
    // From position 3
    $r2 = $this->call($s, 'indexOf', [Runtime::js_str('na'), Runtime::js_int(3)]);
    $this->assertSame(4.0, $r2->toNumber()->value);
  }

  public function testLastIndexOfWithUndefinedAndWithPos(): void
  {
    $s = new jsString(Runtime::js_str('banana'));
    // Undefined pos -> use end of string
    $r1 = $this->call($s, 'lastIndexOf', [Runtime::js_str('na')]);
    $this->assertSame(4.0, $r1->toNumber()->value);
    // With pos 3 -> last 'na' not after index 3, so result 2
    $r2 = $this->call($s, 'lastIndexOf', [Runtime::js_str('na'), Runtime::js_int(3)]);
    $this->assertSame(2.0, $r2->toNumber()->value);
    // Not found
    $r3 = $this->call($s, 'lastIndexOf', [Runtime::js_str('xy')]);
    $this->assertSame(-1.0, $r3->toNumber()->value);
  }

  public function testSliceSubstrSubstring(): void
  {
    $s = new jsString(Runtime::js_str('hello'));
    $this->assertSame('ell', $this->call($s, 'slice', [Runtime::js_int(1), Runtime::js_int(4)])->toStr()->value);
    $this->assertSame('lo',  $this->call($s, 'substr', [Runtime::js_int(3), Runtime::js_int(2)])->toStr()->value);
    // substring should swap if start > end
    $this->assertSame('ell', $this->call($s, 'substring', [Runtime::js_int(4), Runtime::js_int(1)])->toStr()->value);
  }

  public function testSplitWithStringSeparator(): void
  {
    $s = new jsString(Runtime::js_str('a,b,c'));
    $arr = $this->call($s, 'split', [Runtime::js_str(','), Runtime::js_int(2)]);
    $this->assertInstanceOf(jsArray::class, $arr->toObject());
    $this->assertSame('a', $arr->get(0)->toStr()->value);
    $this->assertSame('b', $arr->get(1)->toStr()->value);
    $this->assertSame(2.0, $arr->get('length')->toNumber()->value);
  }

  public function testCaseTransforms(): void
  {
    $s = new jsString(Runtime::js_str('AbC'));
    $this->assertSame('abc', $this->call($s, 'toLowerCase', [])->toStr()->value);
    $this->assertSame('ABC', $this->call($s, 'toUpperCase', [])->toStr()->value);
    // toLocaleUpperCase should exist and behave like toUpperCase here
    $this->assertSame('ABC', $this->call($s, 'toLocaleUpperCase', [])->toStr()->value);
    $this->assertSame('abc', $this->call($s, 'toLocaleLowerCase', [])->toStr()->value);
  }

  public function testFromCharCode(): void
  {
    $r = jsString::fromCharCode(Runtime::js_int(65), Runtime::js_int(66));
    $this->assertSame('AB', $r->toStr()->value);
  }
}
