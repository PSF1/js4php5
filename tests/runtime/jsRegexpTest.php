<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\JS;
use js4php5\runtime\Runtime;
use js4php5\runtime\jsRegexp;
use js4php5\runtime\Base;
use js4php5\runtime\jsRef;

final class jsRegexpTest extends TestCase
{
  protected function setUp(): void
  {
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
    // Bind "this" correctly via jsRef + Runtime::call
    $ref = new jsRef($obj, $method);
    return Runtime::call($ref, $args);
  }

  public function testConstructorSetsFlagsAndSource(): void
  {
    // Use the factory as lo haría el runtime (argumentos Base)
    $re = jsRegexp::object(Runtime::js_str('ab'), Runtime::js_str('gi'));
    $this->assertSame('ab', $re->get('source')->toStr()->value);
    $this->assertTrue($re->get('global')->toBoolean()->value);
    $this->assertTrue($re->get('ignoreCase')->toBoolean()->value);
    $this->assertFalse($re->get('multiline')->toBoolean()->value);

    $this->assertSame(0.0, $re->get('lastIndex')->toNumber()->value);
  }

  public function testToStringRendersPatternAndFlags(): void
  {
    $re = jsRegexp::object(Runtime::js_str('a/b\\c'), Runtime::js_str('im'));

    $s = $this->call($re, 'toString')->toStr()->value;
    $this->assertStringStartsWith('/a\/b\\\\c/', $s);
    $this->assertStringContainsString('i', $s);
    $this->assertStringContainsString('m', $s);
  }

  public function testObjectFactoryWithExistingRegexpAndUndefinedFlagsReturnsSame(): void
  {
    $re = jsRegexp::object(Runtime::js_str('x'), Runtime::js_str('g'));
    $same = jsRegexp::object($re, Runtime::$undefined);
    $this->assertSame($re, $same);
  }

  public function testObjectFactoryWithExistingRegexpAndFlagsThrows(): void
  {
    $this->expectException(\js4php5\runtime\jsException::class);
    $re = jsRegexp::object(Runtime::js_str('x'), Runtime::js_str('g'));
    jsRegexp::object($re, Runtime::js_str('i'));
  }

  public function testConstructorWithNullFlagsDoesNotSetAny(): void
  {
    // Acepta flags nulos (se normalizan a "")
    $re = jsRegexp::object(Runtime::js_str('q'), Runtime::$undefined);

    $this->assertFalse($re->get('global')->toBoolean()->value);
    $this->assertFalse($re->get('ignoreCase')->toBoolean()->value);
    $this->assertFalse($re->get('multiline')->toBoolean()->value);
  }
}
