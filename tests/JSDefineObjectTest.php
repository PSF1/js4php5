<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\JS;
use js4php5\runtime\Runtime;
use js4php5\runtime\jsObject;
use js4php5\runtime\Base;

final class JSDefineObjectTest extends TestCase
{
  protected function setUp(): void
  {
    // Reset the Runtime static state to ensure isolation between tests
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

    // Initialize runtime
    JS::init();
  }

  public function testDefineObjectCreatesObjectAndVariables(): void
  {
    // Define an object with variables only
    JS::defineObject('external', null, [
      'PI' => 3.14159,
      'ZERO' => 0,
      'NAME' => 'js4php5',
      'FLAG' => true,
    ]);

    // Retrieve the object from the global scope
    $ext = Runtime::$global->get('external');
    $this->assertInstanceOf(jsObject::class, $ext->toObject());

    // Check variables and types
    $this->assertSame('js4php5', $ext->get('NAME')->toStr()->value);
    $this->assertSame(0.0, $ext->get('ZERO')->toNumber()->value);
    $this->assertSame(3.14159, $ext->get('PI')->toNumber()->value);
    $this->assertTrue($ext->get('FLAG')->toBoolean()->value);
  }

  public function testDefineObjectRegistersFunctionsOnObject(): void
  {
    // Define a backing PHP function in the global namespace
    if (!function_exists('js_add')) {
      function js_add($a, $b) {
        // $a and $b are Base values; return a Base number
        return Runtime::js_int($a->toNumber()->value + $b->toNumber()->value);
      }
    }

    JS::defineObject('ext', [
      'add' => 'js_add',
    ], null);

    $ext = Runtime::$global->get('ext');
    $fn = $ext->get('add');

    // Function should be a JS object (jsFunction extends Base::OBJECT)
    $this->assertSame(Base::OBJECT, $fn->type);

    // Call the function directly through Runtime::call using the stored function by PHP name
    $jsFn = Runtime::function_id('js_add');
    $this->assertSame(Base::OBJECT, $jsFn->type); // It is a function object

    $res = Runtime::call($jsFn, [Runtime::js_int(2), Runtime::js_int(3)]);
    $this->assertSame(5.0, $res->toNumber()->value);
  }
}
