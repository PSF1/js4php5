<?php

namespace js4php5\runtime;

use Exception;
use js4php5\JS;

class jsFunction extends jsObject
{
  static $constructor;

  protected $name;

  protected $phpname;

  protected $args;

  protected $scope = array();

  function __construct($name = '', $phpname = 'jsi_empty', $args = array(), $scope = null)
  {
    parent::__construct("Function", Runtime::$proto_function);
    if ($scope == null) {
      $scope = Runtime::$contexts[0]->scope_chain;
    }
    $this->name = $name;
    $this->phpname = $phpname;
    $this->args = $args;
    $this->scope = $scope;
    // Store the expected number of arguments as a JS number
    $this->put("length", new Base(Base::NUMBER, count($args)), array("dontdelete", "readonly", "dontenum"));
    // Create a default prototype object with a back-reference to constructor
    $obj = new jsObject("Object");
    $obj->put("constructor", $this, array("dontenum"));
    $this->put("prototype", $obj, array("dontdelete"));
  }

  static function isConstructor()
  {
    return self::$constructor;
  }

  static public function func_object($value)
  {
    throw new jsException(new jsSyntaxError("new Function(..) not implemented"));
  }

  /* When the [[Call]] property for a Function object F is called, the following steps are taken:
     1. Establish a new execution context using F's FormalParameterList, the passed arguments list, and the this value as described in 10.2.3.
     2. Evaluate F's FunctionBody.
     3. Exit the execution context established in step 1, restoring the previous execution context.
  */

  static public function func_toString()
  {
    $obj = Runtime::this();
    if (!($obj instanceof jsFunction)) {
      throw new jsException(new jsTypeError());
    }
    return $obj->defaultValue();
  }

  static public function func_apply($thisArg, $argArray)
  {
    $obj = Runtime::this();
    if (!($obj instanceof jsFunction)) {
      throw new jsException(new jsTypeError());
    }
    if ($thisArg == Runtime::$null || $thisArg == Runtime::$undefined) {
      $thisArg = Runtime::$global;
    } else {
      $thisArg = $thisArg->toObject();
    }
    if ($argArray == Runtime::$null || $argArray == Runtime::$undefined) {
      // No arguments supplied
      $argArray = array();
    } else {
      // Check for a length property and convert to native PHP array
      if ($argArray->hasProperty("length")) {
        $argArray = jsArray::toNativeArray($argArray);
      } else {
        throw new jsException(new jsTypeError("second argument to apply() must be an array"));
      }
    }
    return $obj->_call($thisArg, $argArray);
  }

  static public function func_call($thisArg)
  {
    $obj = Runtime::this();
    if (!($obj instanceof jsFunction)) {
      throw new jsException(new jsTypeError());
    }
    $args = func_get_args();
    array_shift($args); // remove $thisArg
    if ($thisArg == Runtime::$null || $thisArg == Runtime::$undefined) {
      $thisArg = Runtime::$global;
    } else {
      $thisArg = $thisArg->toObject();
    }
    return $obj->_call($thisArg, $args);
  }

  ////////////////////////
  // scriptable methods //
  ////////////////////////

  function construct($args)
  {
    $obj = new jsObject("Object");
    $proto = $this->get("prototype");
    if ($proto->type == Base::OBJECT) {
      $obj->prototype = $proto;
    } else {
      $obj->prototype = Runtime::$proto_object;
    }
    // [[Call]]
    $v = $this->_call($obj, $args, 1);
    if ($v && $v->type == Base::OBJECT) {
      return $v;
    }
    return $obj;
  }

  function _call($that, $args = array(), $constructor = 0)
  {
    jsFunction::$constructor = $constructor;

    // Create new activation object and populate "arguments"
    $var = new jsObject("Activation");
    $arguments = new jsObject();
    $var->put("arguments", $arguments);
    $len = count($args);

    for ($i = 0; $i < count($this->args); $i++) {
      if (!isset($args[$i])) {
        $args[$i] = Runtime::$undefined;
      } else {
        if ($args[$i] instanceof jsRef) {
          // Sanity: arguments should not be references at this point
          echo "<pre>";
          echo "jsRef as $i-th argument of call\n";
          debug_print_backtrace();
          echo "</pre>";
        }
      }
      // Bind named argument
      $var->put($this->args[$i], $args[$i]);

      // Enforce the "changing one changes the other" rule between arguments object and named parameters
      $arguments->slots[$this->args[$i]] = $var->slots[$this->args[$i]];
      $arguments->slots[$i] = $var->slots[$this->args[$i]];
    }

    if ($len > count($this->args)) {
      // Unnamed extra arguments
      for ($i = count($this->args); $i < $len; $i++) {
        $arguments->put($i, $args[$i]);
      }
    }

    $arguments->put("callee", $this, array("dontenum"));
    $arguments->put("length", new Base(Base::NUMBER, $len), array("dontenum"));

    // Prepare scope and new execution context
    $scope = $this->scope;
    array_unshift($scope, $var);
    $context = new jsContext($that, $scope, $var);
    array_unshift(Runtime::$contexts, $context);

    $thrown = null;
    try {
      // Resolve the PHP callback to invoke for this JS function
      $callback = $this->resolvePhpCallback();

      // Ensure the callback is callable
      if (!is_callable($callback)) {
        throw new \TypeError('Invalid PHP callback for JS function "' . $this->name . '"');
      }

      // Invoke the callback with the prepared arguments
      $v = call_user_func_array($callback, $args);

    } catch (Exception $e) {
      $thrown = $e;
    }

    // Restore previous context
    array_shift(Runtime::$contexts);

    // Re-throw if something went wrong
    if ($thrown != null) {
      throw $thrown;
    }

    // Return value or undefined
    return $v ? $v : Runtime::$undefined;
  }

  /**
   * Resolve the configured PHP callback for this jsFunction instance.
   * Supports:
   * - array [Alias, method] for runtime classes (Object/String/Array/Function/Number/Boolean/Date/RegExp/Error...).
   * - string global function: "function_name"
   * - string FQ static method: "Namespace\\Class::method"
   * - string method of current compiled script class: "method"
   *
   * @return callable|string|array
   */
  protected function resolvePhpCallback()
  {
    // If already using the array form, expand known aliases to FQCNs
    if (is_array($this->phpname)) {
      $cls = $this->phpname[0] ?? null;
      $method = $this->phpname[1] ?? null;

      // Map common aliases used in Runtime::start() to their runtime classes
      $aliases = [
        'Runtime'          => 'js4php5\\runtime\\Runtime',
        'Object'           => 'js4php5\\runtime\\jsObject',
        'jsObject'         => 'js4php5\\runtime\\jsObject',
        'Math'             => 'js4php5\\runtime\\jsMath',
        'jsMath'           => 'js4php5\\runtime\\jsMath',
        'String'           => 'js4php5\\runtime\\jsString',
        'jsString'         => 'js4php5\\runtime\\jsString',
        'Array'            => 'js4php5\\runtime\\jsArray',
        'jsArray'          => 'js4php5\\runtime\\jsArray',
        'Function'         => 'js4php5\\runtime\\jsFunction',
        'jsFunction'       => 'js4php5\\runtime\\jsFunction',
        'Number'           => 'js4php5\\runtime\\jsNumber',
        'jsNumber'         => 'js4php5\\runtime\\jsNumber',
        'Boolean'          => 'js4php5\\runtime\\jsBoolean',
        'jsBoolean'        => 'js4php5\\runtime\\jsBoolean',
        'Date'             => 'js4php5\\runtime\\jsDate',
        'jsDate'           => 'js4php5\\runtime\\jsDate',
        'RegExp'           => 'js4php5\\runtime\\jsRegexp',
        'jsRegexp'         => 'js4php5\\runtime\\jsRegexp',
        'Error'            => 'js4php5\\runtime\\jsError',
        'jsError'          => 'js4php5\\runtime\\jsError',
        'EvalError'        => 'js4php5\\runtime\\jsEvalError',
        'jsEvalError'      => 'js4php5\\runtime\\jsEvalError',
        'RangeError'       => 'js4php5\\runtime\\jsRangeError',
        'jsRangeError'     => 'js4php5\\runtime\\jsRangeError',
        'ReferenceError'   => 'js4php5\\runtime\\jsReferenceError',
        'jsReferenceError' => 'js4php5\\runtime\\jsReferenceError',
        'SyntaxError'      => 'js4php5\\runtime\\jsSyntaxError',
        'jsSyntaxError'    => 'js4php5\\runtime\\jsSyntaxError',
        'TypeError'        => 'js4php5\\runtime\\jsTypeError',
        'jsTypeError'      => 'js4php5\\runtime\\jsTypeError',
        'URIError'         => 'js4php5\\runtime\\jsUriError',
        'jsUriError'       => 'js4php5\\runtime\\jsUriError',
      ];

      if (isset($aliases[$cls])) {
        return [$aliases[$cls], $method];
      }

      // Generic fallback: if a runtime class with that short name exists, use it
      $generic = 'js4php5\\runtime\\' . $cls;
      if (is_string($cls) && class_exists($generic)) {
        return [$generic, $method];
      }

      // Return as-is; caller will validate
      return [$cls, $method];
    }

    // If a string, handle multiple possibilities
    if (is_string($this->phpname)) {
      $phpname = $this->phpname;

      // 1) Global PHP function
      if ($phpname !== '' && function_exists($phpname)) {
        return $phpname;
      }

      // 2) Fully-qualified static method "Class::method"
      if (strpos($phpname, '::') !== false && is_callable($phpname)) {
        return $phpname;
      }

      // 3) Static method on the currently compiled script class, if available
      $fqcn = JS::getCurrentScriptFQCN();
      if ($fqcn && class_exists($fqcn) && method_exists($fqcn, $phpname)) {
        return [$fqcn, $phpname];
      }

      // 4) Fallback: return as-is; will be validated by caller
      return $phpname;
    }

    // Unexpected type; return as-is
    return $this->phpname;
  }

  function defaultValue($iggy = null)
  {
    $o = "function " . $this->name . "(";
    $o .= implode(",", $this->args);
    $o .= ") {\n";
    $o .= " [ function body ] \n";
    $o .= "}\n";
    return Runtime::js_str($o);
  }

  function hasInstance($value)
  {
    if ($value->type != Base::OBJECT) {
      return Runtime::$false;
    }
    $obj = $this->get("prototype");
    if ($obj->type != Base::OBJECT) {
      throw new jsException(new jsTypeError('XXX'));
    }
    do {
      $value = $value->prototype;
      if ($value == null) {
        return Runtime::$false;
      }
      if ($obj == $value) {
        return Runtime::$true;
      }
    } while (true);
  }
}
