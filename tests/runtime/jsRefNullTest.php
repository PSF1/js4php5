<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\JS;
use js4php5\runtime\Runtime;
use js4php5\runtime\jsRefNull;
use js4php5\runtime\Base;
use js4php5\runtime\jsException;
use js4php5\runtime\jsReferenceError;

final class jsRefNullTest extends TestCase
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

  public function testGetValueThrowsReferenceError(): void
  {
    $ref = new jsRefNull('notDefined');

    try {
      $ref->getValue();
      $this->fail('Expected jsException was not thrown');
    } catch (jsException $e) {
      // The carried value should be a jsReferenceError
      $this->assertInstanceOf(jsReferenceError::class, $e->value);
    }
  }

  public function testPutValueCreatesGlobalPropertyAndRespectsRet(): void
  {
    $ref = new jsRefNull('X');

    // ret=0: returns null, writes to global
    $r0 = $ref->putValue(Runtime::js_int(5), 0);
    $this->assertNull($r0);
    $this->assertSame(5.0, Runtime::$global->get('X')->toNumber()->value);

    // ret=1: returns written value
    $r1 = $ref->putValue(Runtime::js_int(7), 1);
    $this->assertSame(7.0, $r1->toNumber()->value);
    $this->assertSame(7.0, Runtime::$global->get('X')->toNumber()->value);

    // ret=2: returns previous value
    $r2 = $ref->putValue(Runtime::js_int(9), 2);
    $this->assertSame(7.0, $r2->toNumber()->value);
    $this->assertSame(9.0, Runtime::$global->get('X')->toNumber()->value);
  }
}
