<?php

namespace js4php5\runtime;

class jsNumber extends jsObject
{
  function __construct($value = null)
  {
    parent::__construct("Number", Runtime::$proto_number);
    if ($value == null) {
      $value = Runtime::$zero;
    }
    // Internal [[NumberData]] stored as a Base NUMBER
    $this->value = $value->toNumber();
  }

  static public function object($value)
  {
    // If called as constructor, return wrapper; otherwise return primitive number
    if (jsFunction::isConstructor()) {
      return new jsNumber($value);
    }
    return $value->toNumber();
  }

  ////////////////////////
  // scriptable methods //
  ////////////////////////

  static public function valueOf()
  {
    $obj = Runtime::this();
    if (!($obj instanceof jsNumber)) {
      throw new jsException(new jsTypeError());
    }
    // Return the wrapped primitive number (Base NUMBER)
    return $obj->value;
  }

  static public function toExponential($digits)
  {
    $obj = Runtime::this();
    $f = (int)$digits->toInteger()->value;
    if ($f < 0 || $f > 20) {
      // Throw RangeError like JS (wrapped into jsException)
      throw new jsException(new jsRangeError());
    }
    $x = $obj->toNumber()->value;
    if (is_nan($x)) {
      return Runtime::js_str("NaN");
    }
    if (is_infinite($x)) {
      return self::toString();
    }
    // 1 leading digit + f fraction digits in exponential form
    return Runtime::js_str(sprintf("%." . (1 + $f) . "e", $x));
  }

  static public function toString()
  {
    // Get optional radix argument (may be undefined or absent)
    $args = func_get_args();
    $radixArg = $args[0] ?? Runtime::$undefined;

    $obj = Runtime::this();
    if (!($obj instanceof jsNumber)) {
      throw new jsException(new jsTypeError());
    }
    $x = $obj->toNumber()->value;

    if (is_nan($x)) {
      return Runtime::js_str("NaN");
    }
    if ($x == 0) {
      return Runtime::js_str("0");
    }
    if ($x < 0 && is_infinite($x)) {
      return Runtime::js_str("-Infinity");
    }
    if (is_infinite($x)) {
      return Runtime::js_str("Infinity");
    }

    // Determine radix
    if ($radixArg === Runtime::$undefined) {
      $radix = 10;
    } else {
      $radix = (int)$radixArg->toNumber()->value;
    }
    if ($radix < 2 || $radix > 36) {
      $radix = 10;
    }

    // Radix 10: default string cast
    if ($radix === 10) {
      return Runtime::js_str((string)$x);
    }

    // For non-decimal radices we only handle integer part; for non-integers fallback to decimal string
    $neg = ($x < 0);
    $absx = abs($x);
    if (floor($absx) != $absx) {
      // Fallback: there is no trivial fractional conversion; return decimal string
      return Runtime::js_str((string)$x);
    }

    $converted = base_convert((string)(int)$absx, 10, $radix);
    if ($neg && $converted[0] !== '-') {
      $converted = '-' . $converted;
    }
    return Runtime::js_str($converted);
  }

  static public function toPrecision($digits)
  {
    $obj = Runtime::this();
    if ($digits == Runtime::$undefined) {
      // Behaves like toString() when precision is undefined
      return self::toString($digits);
    }
    $f = (int)$digits->toInteger()->value;
    if ($f < 1 || $f > 21) {
      throw new jsException(new jsRangeError());
    }
    $x = $obj->toNumber()->value;
    if (is_nan($x)) {
      return Runtime::js_str("NaN");
    }
    if (is_infinite($x)) {
      return self::toString();
    }
    // Heuristic preserved from original: use exponential for very large/small numbers
    if ($x > (float)("1e$f") || $x < 1e-6) {
      return Runtime::js_str(sprintf("%." . $f . "e", $x));
    }
    // Otherwise, delegate to toFixed(f)
    return self::toFixed($digits);
  }

  static public function toFixed($digits)
  {
    $obj = Runtime::this();
    $f = (int)$digits->toInteger()->value;
    if ($f < 0 || $f > 20) {
      throw new jsException(new jsRangeError());
    }
    $x = $obj->toNumber()->value;
    if (is_nan($x)) {
      return Runtime::js_str("NaN");
    }
    if (is_infinite($x)) {
      return self::toString();
    }

    // Cheap version: string manipulation without rounding of fractional part beyond desired precision
    $s = (string)$x;
    if (strpos($s, ".") === false) {
      // Use $f (int), not the Base $digits parameter
      return Runtime::js_str($s . "." . str_repeat("0", $f));
    }
    [$intPart, $fracPart] = explode(".", $s, 2);
    if ($f > strlen($fracPart)) {
      return Runtime::js_str($intPart . "." . $fracPart . str_repeat("0", $f - strlen($fracPart)));
    }
    return Runtime::js_str($intPart . "." . substr($fracPart, 0, $f));
  }

  function defaultValue($iggy = null)
  {
    // When converted to primitive, return the wrapped number
    return $this->value;
  }
}
