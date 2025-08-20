<?php

namespace js4php5\runtime;

use js4php5\VarDumper;

class Base
{
  // Using string constants during development for easier review of object dumps.
  // This has almost no impact on speed.
  const UNDEFINED = 'undefined';
  const NULL = 'null';
  const BOOLEAN = 'boolean';
  const NUMBER = 'number';
  const STRING = 'string';
  const OBJECT = 'object';
  const REF = 'ref';

  /** @var int|string Type of $value; one of the Base:: constants. */
  public $type;

  /** @var mixed */
  public $value;

  /** @var array<string, jsAttribute> Only used if type is Base::OBJECT. */
  public $slots;

  /**
   * @param int|string $type
   * @param mixed $value
   */
  function __construct($type, $value)
  {
    $this->type = $type;
    $this->value = $value;
  }

  /**
   * Returns the short class name (without namespace).
   */
  public static function className()
  {
    $x = explode('\\', get_called_class());
    return end($x);
  }

  /**
   * Convert to boolean following JavaScript rules.
   */
  function toBoolean()
  {
    switch ($this->type) {
      case Base::UNDEFINED:
      case Base::NULL:
        return Runtime::$false;
      case Base::OBJECT:
        return Runtime::$true;
      case Base::BOOLEAN:
        return $this;
      case Base::NUMBER:
        return ($this->value == 0 || is_nan($this->value)) ? Runtime::$false : Runtime::$true;
      case Base::STRING:
        // Ensure length checks are done on a string (avoid deprecations if value were null)
        return (strlen((string)$this->value) == 0) ? Runtime::$false : Runtime::$true;
    }
  }

  /**
   * Convert to 32-bit signed integer following JavaScript rules.
   */
  function toInt32()
  {
    $v = $this->toInteger();
    if (is_infinite($v->value)) {
      return Runtime::$zero;
    }
    // Store as JS number (float)
    return Runtime::js_int((int)$v->value);
  }

  /**
   * Convert to integer following JavaScript rules.
   */
  function toInteger()
  {
    $v = $this->toNumber();
    if (is_nan($v->value)) {
      return Runtime::$zero;
    }
    if ($v->value == 0 || is_infinite($v->value)) {
      return $v;
    }
    // sign(x) * floor(abs(x))
    return Runtime::js_int(($v->value / abs($v->value)) * floor(abs($v->value)));
  }

  /**
   * Convert to number following JavaScript rules.
   */
  function toNumber()
  {
    switch ($this->type) {
      case Base::UNDEFINED:
        return Runtime::$nan;
      case Base::NULL:
        return Runtime::$zero;
      case Base::BOOLEAN:
        return $this->value ? Runtime::$one : Runtime::$zero;
      case Base::NUMBER:
        return $this;
      case Base::STRING:
        // Cast to float for numeric strings; otherwise return NaN
        return is_numeric($this->value) ? Runtime::js_int((float)$this->value) : Runtime::$nan;
      case Base::OBJECT:
        return $this->toPrimitive(Base::NUMBER)->toNumber();
    }
  }

  /**
   * Convert to primitive following JavaScript rules.
   *
   * @param null|int|string $hint Base::TYPE constant defining the value type.
   */
  function toPrimitive($hint = null)
  {
    if ($this->type != Base::OBJECT) {
      return $this;
    }
    if ($hint != null) {
      return $this->defaultValue($hint);
    }
    return $this->defaultValue();
  }

  /**
   * Convert to unsigned 32-bit integer following JavaScript rules.
   */
  function toUInt32()
  {
    $v = $this->toInteger();
    if (is_infinite($v->value)) {
      return Runtime::$zero;
    }
    // Use floating modulus to avoid dependency on bcmath and avoid string warnings
    $n = (float)$v->value;
    $mod = 4294967296.0; // 2^32
    // fmod handles large numbers; normalize into [0, 2^32)
    $n = fmod($n, $mod);
    if ($n < 0) {
      $n += $mod;
    }
    return Runtime::js_int($n);
  }

  /**
   * Convert to unsigned 16-bit integer following JavaScript rules.
   */
  function toUInt16()
  {
    $v = $this->toInteger();
    if (is_infinite($v->value)) {
      return Runtime::$zero;
    }
    $n = (float)$v->value;
    $mod = 65536.0; // 2^16
    $n = fmod($n, $mod);
    if ($n < 0) {
      $n += $mod;
    }
    return Runtime::js_int($n);
  }

  /**
   * Convert to string following JavaScript rules.
   */
  function toStr()
  {
    switch ($this->type) {
      case Base::UNDEFINED:
        return Runtime::js_str("undefined");
      case Base::NULL:
        return Runtime::js_str("null");
      case Base::BOOLEAN:
        return Runtime::js_str($this->value ? "true" : "false");
      case Base::STRING:
        return $this;
      case Base::OBJECT:
        return $this->toPrimitive(Base::STRING)->toStr();
      case Base::NUMBER:
        if (is_nan($this->value)) {
          return Runtime::js_str("NaN");
        }
        if ($this->value == 0) {
          return Runtime::js_str("0");
        }
        if ($this->value < 0) {
          $v = Runtime::js_int(-$this->value)->toStr();
          return Runtime::js_str("-" . $v->value);
        }
        if (is_infinite($this->value)) {
          return Runtime::js_str("Infinity");
        }
        return Runtime::js_str((string)$this->value);
    }
  }

  /**
   * Convert to object following JavaScript rules.
   *
   * @throws jsException
   */
  function toObject()
  {
    switch ($this->type) {
      case Base::UNDEFINED:
      case Base::NULL:
        throw new jsException(new jsTypeError("Cannot convert null or undefined to objects"));
      case Base::BOOLEAN:
        return new jsBoolean($this);
      case Base::NUMBER:
        return new jsNumber($this);
      case Base::STRING:
        return new jsString($this);
      case Base::OBJECT:
        return $this;
    }
    throw new jsException(new jsTypeError("Do not know how to convert value of type '{$this->type}'."));
  }

  /**
   * Simple debug output.
   */
  function toDebug()
  {
    switch ($this->type) {
      case Base::UNDEFINED:
        return "undefined";
      case Base::NULL:
        return "null";
      case Base::BOOLEAN:
        return $this->value ? "true" : "false";
      case Base::NUMBER:
        return $this->value;
      case Base::STRING:
        return var_export($this->value, true);
      case Base::OBJECT:
        // Avoid passing function result by reference to array_pop/end
        $parts = explode('\\', get_class($this));
        $classShort = end($parts);
        $s = "class: " . $classShort . "<br>";
        if (is_array($this->slots)) {
          foreach ($this->slots as $key => $value) {
            // $value is a jsAttribute, show contained value
            $s .= "$key => " . $value->value . "<br>";
          }
        }
        return $s;
    }
  }

  /**
   * In JS, objects are values; return self.
   */
  function getValue()
  {
    return $this;
  }

  /**
   * In JS, assigning to a non-reference should not happen here.
   * We throw a ReferenceError including a dump of the value.
   *
   * @throws jsException
   */
  function putValue($w)
  {
    throw new jsException(new jsReferenceError(VarDumper::dumpAsString($w)));
  }
}
