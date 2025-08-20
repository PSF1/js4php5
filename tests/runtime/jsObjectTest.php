<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\JS;
use js4php5\runtime\Runtime;
use js4php5\runtime\jsObject;
use js4php5\runtime\jsAttribute;
use js4php5\runtime\Base;
use js4php5\runtime\jsRef;

final class jsObjectTest extends TestCase
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

  public function testConstructorSetsPrototypeByClass(): void
  {
    $oObj = new jsObject('Object');
    $this->assertSame(Runtime::$proto_object, $oObj->prototype);

    $aObj = new jsObject('Array');
    $this->assertSame(Runtime::$proto_array, $aObj->prototype);

    $sObj = new jsObject('String');
    $this->assertSame(Runtime::$proto_string, $sObj->prototype);

    $eObj = new jsObject('EvalError');
    // EvalError should change class to "Error" and prototype to proto_evalerror (then class becomes "Error")
    $this->assertSame('Error', $eObj->class);
    $this->assertSame(Runtime::$proto_evalerror, $eObj->prototype);
  }

  public function testConstructorHonorsExplicitPrototypeArgument(): void
  {
    // Force a specific prototype
    $customProto = Runtime::$proto_string;
    $obj = new jsObject('Object', $customProto);
    $this->assertSame($customProto, $obj->prototype);
  }

  public function testToStringReturnsBracketedClassName(): void
  {
    $arrObj = new jsObject('Array');

    // Call Object.prototype.toString with "this" bound to the array
    // This returns "[object Array]" per spec
    $objectProtoToString = Runtime::$proto_object->get('toString'); // jsFunction
    $sArr = $objectProtoToString->_call($arrObj, []);               // bind "this" manually
    $this->assertSame('[object Array]', $sArr->toStr()->value);

    // For plain objects, the property lookup already resolves to Object.prototype.toString
    $obj = new jsObject('Object');
    $ref = new jsRef($obj, 'toString');
    $sObj = Runtime::call($ref, []);
    $this->assertSame('[object Object]', $sObj->toStr()->value);
  }

  public function testPutGetDeleteAndAttributes(): void
  {
    $obj = new jsObject();
    // Set property with flags
    $obj->put('x', Runtime::js_int(1), ['dontenum']);
    $this->assertSame(1.0, $obj->get('x')->toNumber()->value);

    // propertyIsEnumerable should be false due to dontenum
    $ref = new jsRef($obj, 'propertyIsEnumerable');
    $res = Runtime::call($ref, [Runtime::js_str('x')]);
    $this->assertFalse($res->toBoolean()->value);

    // Readonly prevents overwriting
    $obj->put('y', Runtime::js_int(2), ['readonly']);
    $this->assertFalse($obj->canPut('y'));

    // dontdelete prevents deletion
    $obj->put('z', Runtime::js_int(3), ['dontdelete']);
    $this->assertFalse($obj->delete('z'));

    // Deleting non-existing returns true
    $this->assertTrue($obj->delete('nope'));
  }
}
