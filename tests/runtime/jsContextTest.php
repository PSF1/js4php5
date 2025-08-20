<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\JS;
use js4php5\runtime\Runtime;
use js4php5\runtime\jsContext;
use js4php5\runtime\jsObject;

final class jsContextTest extends TestCase
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

  public function testConstructorAssignsProperties(): void
  {
    $that = new jsObject();
    $scope = [new jsObject()];
    $var = new jsObject();

    $ctx = new jsContext($that, $scope, $var);

    $this->assertSame($that, $ctx->js_this);
    $this->assertSame($scope, $ctx->scope_chain);
    $this->assertSame($var, $ctx->var);
  }

  public function testPushAndPopContext(): void
  {
    $obj = new jsObject();
    Runtime::push_context($obj);

    $this->assertInstanceOf(jsContext::class, Runtime::$contexts[0]);
    $this->assertSame($obj, Runtime::$contexts[0]->var);

    Runtime::pop_context();

    // After pop, there should still be a base context from JS::init()
    $this->assertNotEmpty(Runtime::$contexts);
    $this->assertInstanceOf(jsContext::class, Runtime::$contexts[0]);
  }
}
