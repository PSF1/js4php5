<?php

namespace js4php5\runtime;

class jsError extends jsObject
{
  function __construct($class = "Error", $proto = null, $msg = '')
  {
    parent::__construct($class, ($proto == null) ? Runtime::$proto_error : $proto);
    // Initialize standard properties
    $this->put("name", Runtime::js_str($class));
    $this->put("message", Runtime::js_str($msg));
  }

  ////////////////////////
  // scriptable methods //
  ////////////////////////

  static function object($message)
  {
    // Factory/constructor for Error(message)
    return new jsError("Error", null, $message->toStr()->value);
  }

  static function toString()
  {
    $obj = Runtime::this();
    if (!($obj instanceof jsError)) {
      // Throw a TypeError if "this" is not an Error object
      throw new jsException(new jsTypeError());
    }

    // Build "Name: message" using the object's properties
    $name = $obj->get("name")->toStr()->value;
    $msg  = $obj->get("message")->toStr()->value;

    return Runtime::js_str($name . ": " . $msg);
  }
}
