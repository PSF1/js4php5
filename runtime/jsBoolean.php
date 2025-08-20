<?php

namespace js4php5\runtime;

class jsBoolean extends jsObject
{
  function __construct($value = null)
  {
    parent::__construct("Boolean", Runtime::$proto_boolean);
    if ($value == null) {
      // Default to undefined -> false
      $value = Runtime::$undefined;
    }
    // Store the internal [[BooleanData]] as a Base BOOLEAN
    $this->value = $value->toBoolean();
  }

  static public function object($value)
  {
    // If called as constructor (new Boolean(...)), return a wrapper object; otherwise return a primitive boolean
    if (jsFunction::isConstructor()) {
      return new jsBoolean($value);
    }
    return $value->toBoolean();
  }

  ////////////////////////
  // scriptable methods //
  ////////////////////////

  static public function toString()
  {
    $obj = Runtime::this();
    // Ensure "this" is a Boolean object
    if (!($obj instanceof jsBoolean)) {
      throw new jsException(new jsTypeError());
    }
    // Return "true" or "false" according to the wrapped boolean value
    return $obj->value->value ? Runtime::js_str("true") : Runtime::js_str("false");
  }

  static public function valueOf()
  {
    $obj = Runtime::this();
    // Ensure "this" is a Boolean object
    if (!($obj instanceof jsBoolean)) {
      throw new jsException(new jsTypeError());
    }
    // Return the wrapped primitive boolean (Base BOOLEAN)
    return $obj->value;
  }

  function defaultValue($iggy = null)
  {
    // When a Boolean object is converted to a primitive, return the wrapped boolean
    return $this->value;
  }
}
