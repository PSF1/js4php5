<?php

namespace js4php5\runtime;

use Iterator;

class jsObject extends Base implements Iterator
{
  /** @var jsObject|null */
  public $prototype = null;

  /** @var string */
  public $class = "Object";

  /**
   * @param string        $class
   * @param null|jsObject $proto
   */
  function __construct($class = "Object", $proto = null)
  {
    parent::__construct(Base::OBJECT, null);

    // Initialize slots storage as an array to avoid undefined access
    if (!is_array($this->slots)) {
      $this->slots = [];
    }

    // Select default prototype based on class name
    switch ($class) {
      default: /* default to Object */
      case "Object":
        $this->prototype = Runtime::$proto_object;
        break;
      case "Function":
        $this->prototype = Runtime::$proto_function;
        break;
      case "Array":
        $this->prototype = Runtime::$proto_array;
        break;
      case "String":
        $this->prototype = Runtime::$proto_string;
        break;
      case "Boolean":
        $this->prototype = Runtime::$proto_boolean;
        break;
      case "Number":
        $this->prototype = Runtime::$proto_number;
        break;
      case "Date":
        $this->prototype = Runtime::$proto_date;
        break;
      case "RegExp":
        $this->prototype = Runtime::$proto_regexp;
        break;
      case "Error":
        $this->prototype = Runtime::$proto_error;
        break;
      case "EvalError":
        $this->prototype = Runtime::$proto_evalerror;
        $class = "Error";
        break;
      case "RangeError":
        $this->prototype = Runtime::$proto_rangeerror;
        $class = "Error";
        break;
      case "ReferenceError":
        $this->prototype = Runtime::$proto_referenceerror;
        $class = "Error";
        break;
      case "SyntaxError":
        $this->prototype = Runtime::$proto_syntaxerror;
        $class = "Error";
        break;
      case "TypeError":
        $this->prototype = Runtime::$proto_typeerror;
        $class = "Error";
        break;
      case "URIError":
        $this->prototype = Runtime::$proto_urierror;
        $class = "Error";
        break;
    }

    // Store final class name (may be changed for Error subclasses)
    $this->class = $class;

    // If an explicit prototype is provided, override the default one;
    // otherwise, KEEP the class-based prototype selected above.
    if ($proto !== null) {
      $this->prototype = $proto;
    }
  }

  static public function object($value)
  {
    if ($value != Runtime::$null && $value != Runtime::$undefined) {
      return $value->toObject();
    }
    // Back to our regularly scheduled constructor.
    return new jsObject("Object");
  }

  static public function toString()
  {
    $obj = Runtime::this();
    // Use Runtime::js_str helper (no global js_str function exists)
    return Runtime::js_str("[object " . $obj->class . "]");
  }

  static public function valueOf()
  {
    return Runtime::this();
  }

  static public function hasOwnProperty($value)
  {
    $obj = Runtime::this();
    $name = $value->toStr()->value;
    return (isset($obj->slots[$name])) ? Runtime::$true : Runtime::$false;
  }

  static public function isPrototypeOf($value)
  {
    $obj = Runtime::this();
    if ($value->type != Base::OBJECT) {
      return Runtime::$false;
    }
    do {
      $value = $value->prototype;
      if ($value == null) {
        return Runtime::$false;
      }
      if ($obj === $value) {
        return Runtime::$true;
      }
    } while (true);
  }

  static public function propertyIsEnumerable($value)
  {
    $obj = Runtime::this();
    $name = $value->toStr()->value;
    if (!isset($obj->slots[$name])) {
      return Runtime::$false;
    }
    $attr = $obj->slots[$name];
    return $attr->dontenum ? Runtime::$false : Runtime::$true;
  }

  function put($name, $value, $opts = null)
  {
    $name = (string)$name;

    if (!$this->canPut($name)) {
      return;
    }

    if ($value instanceof jsRef) {
      // Debug aid: value should not be a jsRef here
      echo "<pre>";
      debug_print_backtrace();
      echo "</pre>";
    }

    if (isset($this->slots[$name])) {
      $o = $this->slots[$name];
      $o->value = $value;
    } else {
      $o = new jsAttribute($value);
      $this->slots[$name] = $o;
    }

    if ($opts) {
      foreach ($opts as $opt) {
        $o->$opt = true; // Set flags: readonly/dontenum/dontdelete
      }
    }
  }

  //////////////////////
  // Iterator interface
  //////////////////////

  function canPut($name)
  {
    $name = (string)$name;
    if (isset($this->slots[$name])) {
      return $this->slots[$name]->readonly == false;
    }
    if ($this->prototype == null) {
      return true;
    }
    return $this->prototype->canPut($name);
  }

  function hasProperty($name)
  {
    $key = (string)$name;
    if (isset($this->slots[$key])) {
      return true;
    }
    if ($this->prototype == null) {
      return false;
    }
    return $this->prototype->hasProperty($key);
  }

  function delete($name)
  {
    $name = (string)$name;
    if (!isset($this->slots[$name])) {
      return true;
    }
    if ($this->slots[$name]->dontdelete) {
      return false;
    }
    unset($this->slots[$name]);
    return true;
  }

  function defaultValue($hint = Base::NUMBER)
  {
    switch ($hint) {
      case Base::STRING:
        $v = $this->pcall("toString");
        if ($v != Runtime::$undefined) {
          return $v;
        }
        $v = $this->pcall("valueOf");
        if ($v != Runtime::$undefined) {
          return $v;
        }
        break;
      case Base::NUMBER:
        $v = $this->pcall("valueOf");
        if ($v != Runtime::$undefined) {
          return $v;
        }
        $v = $this->pcall("toString");
        if ($v != Runtime::$undefined) {
          return $v;
        }
        break;
    }
    // Try toSource() as a last resort (non-standard)
    return $this->pcall("toSource");
  }

  protected function pcall($n)
  {
    $p = $this->get($n);
    if ($p->type == Base::OBJECT) {
      $v = $p->_call($this);
      if ($v->type != Base::OBJECT) {
        return $v;
      }
    }
    return Runtime::$undefined;
  }

  ////////////////////////
  // scriptable methods //
  ////////////////////////

  function get($name)
  {
    $name = (string)$name;
    if (isset($this->slots[$name])) {
      return $this->slots[$name]->value;
    }
    if ($this->prototype == null) {
      return Runtime::$undefined;
    }
    return $this->prototype->get($name);
  }

  // PHP 8.1+: keep signature without return type
  #[\ReturnTypeWillChange]
  public function rewind()
  {
    reset($this->slots);
  }

  #[\ReturnTypeWillChange]
  public function current()
  {
    $attr = current($this->slots);
    return $attr ? key($this->slots) : Runtime::$undefined;
  }

  #[\ReturnTypeWillChange]
  public function key()
  {
    return key($this->slots);
  }

  #[\ReturnTypeWillChange]
  public function next()
  {
    do {
      $attr = next($this->slots);
    } while ($attr && $attr->dontenum);
    return $attr ? key($this->slots) : Runtime::$undefined;
  }

  #[\ReturnTypeWillChange]
  public function valid()
  {
    return (key($this->slots) !== null);
  }
}
