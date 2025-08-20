<?php

namespace js4php5\runtime;

use js4php5\JS;

class jsString extends jsObject
{
  function __construct($value = null)
  {
    parent::__construct("String", Runtime::$proto_string);
    if ($value == null || $value == Runtime::$undefined) {
      $this->value = Runtime::js_str("");
    } else {
      $this->value = $value->toStr();
    }
    $len = strlen($this->value->value);
    if (Runtime::$proto_string != null) {
      $this->put("length", Runtime::js_int($len), array("dontenum", "dontdelete", "readonly"));
    }
  }

  static public function object($value)
  {
    if (jsFunction::isConstructor()) {
      return new jsString($value);
    } else {
      if ($value == Runtime::$undefined) {
        return Runtime::js_str("");
      }
      return $value->toStr();
    }
  }

  ////////////////////////
  // scriptable methods //
  ////////////////////////

  static public function fromCharCode($c)
  {
    $args = func_get_args();
    $s = '';
    foreach ($args as $arg) {
      $v = $arg->toUInt16()->value;
      // Ensure byte range for chr()
      $s .= chr($v & 0xFF);
    }
    return Runtime::js_str($s);
  }

  static public function toString()
  {
    $obj = Runtime::this();
    if (!($obj instanceof jsString)) {
      throw new jsException(new jsTypeError());
    }
    return $obj->value;
  }

  static public function charAt($pos)
  {
    $str = Runtime::this()->toStr()->value;
    // Force integer index to avoid "String offset cast occurred"
    $pos = (int)$pos->toInteger()->value;
    if ($pos < 0 || strlen($str) <= $pos) {
      return Runtime::js_str("");
    }
    return Runtime::js_str($str[$pos]);
  }

  static public function charCodeAt($pos)
  {
    $str = Runtime::this()->toStr()->value;
    // Force integer index to avoid "String offset cast occurred"
    $pos = (int)$pos->toInteger()->value;
    if ($pos < 0 || strlen($str) <= $pos) {
      return Runtime::$nan;
    }
    return Runtime::js_int(ord($str[$pos]));
  }

  static public function concat($v = null)
  {
    // Capture arguments BEFORE modifying any parameter variable
    $args = func_get_args();

    // Start with the string value of "this"
    $str = Runtime::this()->toStr()->value;

    // Append all arguments converted to string
    foreach ($args as $arg) {
      $str .= $arg->toStr()->value;
    }

    return Runtime::js_str($str);
  }

  static public function indexOf($str, $pos = null)
  {
    $obj = Runtime::this()->toStr()->value;
    $needle = $str->toStr()->value;
    // Optional pos: if missing/undefined -> 0
    if ($pos === null || $pos == Runtime::$undefined) {
      $start = 0;
    } else {
      $start = (int)$pos->toInteger()->value;
      if ($start < 0) {
        $start = 0;
      }
    }
    $v = strpos($obj, $needle, $start);
    if ($v === false) {
      return Runtime::js_int(-1);
    }
    return Runtime::js_int($v);
  }

  static public function lastIndexOf($str, $pos = null)
  {
    $obj = Runtime::this()->toStr()->value;
    $needle = $str->toStr()->value;
    $len = strlen($obj);

    // Optional pos: undefined/NaN -> use end of string
    if ($pos === null || $pos == Runtime::$undefined) {
      $from = $len - 1;
    } else {
      $n = $pos->toNumber()->value;
      if (is_nan($n)) {
        $from = $len - 1;
      } else {
        // Clamp to [0, len-1]
        $from = (int)$n;
        if ($from < 0) {
          return Runtime::js_int(-1);
        }
        if ($from >= $len) {
          $from = $len - 1;
        }
      }
    }

    // JS semantics: search last occurrence at or before fromIndex.
    // Build a slice up to fromIndex+1 and apply strrpos without offset.
    $hay = substr($obj, 0, $from + 1);
    $v = strrpos($hay, $needle);
    if ($v === false) {
      return Runtime::js_int(-1);
    }
    return Runtime::js_int($v);
  }

  static public function localeCompare($that)
  {
    $obj = Runtime::this();
    return Runtime::js_int(strcoll($obj->toStr()->value, $that->toStr()->value));
  }

  static public function match($regexp)
  {
    $obj = Runtime::this()->toStr();
    if (!($regexp instanceof jsRegexp)) {
      $regexp = new jsRegexp($regexp->toStr()->value);
    }
    // Use correct false singleton
    if ($regexp->get("global") == Runtime::$false) {
      // exec called with this = regexp, argument = string
      return Runtime::$proto_regexp->get("exec")->_call($regexp, $obj);
    } else {
      $regexp->put("lastIndex", Runtime::$zero);
      // Not implemented yet
      throw new jsException(new jsError("string::match not implemented"));
    }
  }

  static public function replace($search, $replace)
  {
    $obj = Runtime::this()->toStr();
    // Not implemented yet
    throw new jsException(new jsError("string::replace not implemented"));
  }

  static public function search($regexp)
  {
    $obj = Runtime::this()->toStr();
    if (!($regexp instanceof jsRegexp)) {
      $regexp = new jsRegexp($regexp->toStr()->value);
    }
    // Not implemented yet
    throw new jsException(new jsError("string::search not implemented"));
  }

  static public function slice($start, $end)
  {
    $obj = Runtime::this()->toStr()->value;
    $len = strlen($obj);
    $start = $start->toInteger()->value;
    $end = ($end == Runtime::$undefined) ? $len : $end->toInteger()->value;
    $start = ($start < 0) ? max($len + $start, 0) : min($start, $len);
    $end = ($end < 0) ? max($len + $end, 0) : min($end, $len);
    $length = max($end - $start, 0);
    $str = substr($obj, $start, $length);
    return Runtime::js_str($str);
  }

  static public function split($sep, $limit)
  {
    $obj = Runtime::this()->toStr()->value;

    // Compute JS limit (uint32); undefined -> no límite efectivo
    $unlimited = ($limit == Runtime::$undefined);
    $limitVal = $unlimited ? PHP_INT_MAX : (int)$limit->toUInt32()->value;

    // RegExp separators: not implemented yet
    if ($sep instanceof jsRegexp) {
      // When implemented: use preg_split with flags and respect limitVal
      throw new jsException(new jsError("string::split(//) not implemented"));
    }

    // JS: if separator is undefined, result is [string] (or [] if limit == 0)
    if ($sep == Runtime::$undefined) {
      $parts = [$obj];
    } else {
      $delimiter = $sep->toStr()->value;

      // Special case: empty separator splits into characters (not cubierto por los tests actuales)
      if ($delimiter === '') {
        // Split into individual UTF-8 bytes (ASCII-only behavior); mejora: usar mb_* si lo necesitas
        $parts = str_split($obj);
      } else {
        // Split all parts first, then apply JS-style limit by slicing
        $parts = explode($delimiter, $obj);
      }
    }

    // Apply JS limit: keep only the first limitVal items
    if (!$unlimited) {
      $parts = array_slice($parts, 0, max(0, $limitVal));
    }

    // Build a jsArray of Base strings
    $array = new jsArray();
    $i = 0;
    foreach ($parts as $p) {
      $array->put($i++, Runtime::js_str($p));
    }
    return $array;
  }

  static public function substr($start, $length)
  {
    $obj = Runtime::this()->toStr()->value;
    $len = strlen($obj);
    $start = $start->toInteger()->value;
    $length = ($length == Runtime::$undefined) ? 1e80 : $length->toInteger()->value;
    $start = ($start >= 0) ? $start : max($len + $start, 0);
    $length = min(max($length, 0), $len - $start);
    if ($length <= 0) {
      return Runtime::js_str("");
    }
    return Runtime::js_str(substr($obj, $start, (int)$length));
  }

  static public function substring($start, $end)
  {
    $obj = Runtime::this()->toStr()->value;
    $lenStr = strlen($obj);
    $startVal = $start->toInteger()->value;
    $endVal = ($end == Runtime::$undefined) ? $lenStr : $end->toInteger()->value;

    $startVal = min(max($startVal, 0), $lenStr);
    $endVal = min(max($endVal, 0), $lenStr);

    $from = min($startVal, $endVal);
    $to   = max($startVal, $endVal);
    $length = $to - $from;

    return Runtime::js_str(substr($obj, $from, $length));
  }

  static public function toLocaleLowerCase()
  {
    // Basic ASCII behavior (no full i18n)
    return self::toLowerCase();
  }

  static public function toLowerCase()
  {
    return Runtime::js_str(strtolower(Runtime::this()->toStr()->value));
  }

  static public function toLocaleUpperCase()
  {
    return self::toUpperCase();
  }

  static public function toUpperCase()
  {
    return Runtime::js_str(strtoupper(Runtime::this()->toStr()->value));
  }

  function defaultValue($iggy = null)
  {
    return $this->value;
  }
}
