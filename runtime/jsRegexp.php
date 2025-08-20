<?php

namespace js4php5\runtime;

class jsRegexp extends jsObject
{
  /** @var string */
  public $pattern;

  /** @var string */
  public $flags;

  function __construct($pattern = null, $flags = null)
  {
    parent::__construct("RegExp", Runtime::$proto_regexp);

    // Accept Base or scalar and normalize to strings
    $pat = ($pattern instanceof Base) ? $pattern->toStr()->value : (string)$pattern;
    $flg = ($flags   instanceof Base) ? $flags->toStr()->value   : (string)$flags;

    $this->pattern = $pat;
    $this->flags   = $flg;

    // Helper to assign own property with flags (bypass canPut/prototype readonly)
    $setOwn = function(string $name, $value, array $opts = []) {
      $attr = new jsAttribute($value);
      foreach ($opts as $opt) {
        $attr->$opt = true;
      }
      // Write directly to own slots to avoid prototype's readonly blocking the assignment.
      $this->slots[$name] = $attr;
    };

    // Flags as booleans; define as own, non-enumerable, readonly (except lastIndex)
    $setOwn('global',
      (strpos($flg, 'g') !== false) ? Runtime::$true : Runtime::$false,
      ['dontdelete','readonly','dontenum']
    );
    $setOwn('ignoreCase',
      (strpos($flg, 'i') !== false) ? Runtime::$true : Runtime::$false,
      ['dontdelete','readonly','dontenum']
    );
    $setOwn('multiline',
      (strpos($flg, 'm') !== false) ? Runtime::$true : Runtime::$false,
      ['dontdelete','readonly','dontenum']
    );
    $setOwn('source',
      Runtime::js_str($pat),
      ['dontdelete','readonly','dontenum']
    );
    // lastIndex es writable según spec (no readonly)
    $setOwn('lastIndex',
      Runtime::$zero,
      ['dontdelete','dontenum']
    );
  }

  ////////////////////////
  // scriptable methods //
  ////////////////////////

  static function object($value)
  {
    list ($pattern, $flags) = func_get_args();

    if (!jsFunction::isConstructor() && ($pattern instanceof jsRegexp) && $flags == Runtime::$undefined) {
      return $pattern;
    }
    if ($pattern instanceof jsRegexp) {
      if ($flags != Runtime::$undefined) {
        throw new jsException(new jsTypeError());
      }
      $flags = $pattern->flags;
      $pattern = $pattern->pattern;
    } else {
      $flags = ($flags == Runtime::$undefined) ? "" : $flags->toStr()->value;
      $pattern = ($pattern == Runtime::$undefined) ? "" : $pattern->toStr()->value;
    }
    return new jsRegexp($pattern, $flags);
  }

  static function test($str)
  {
    return (jsRegexp::exec($str) != null) ? Runtime::$true : Runtime::$false;
  }

  static function exec($str)
  {
    $obj = Runtime::this();
    if (!($obj instanceof jsRegexp)) {
      throw new jsException(new jsTypeError());
    }
    $s = $str->toStr()->value;
    $len = strlen($s);
    $lastIndex = $obj->get("lastIndex")->toInteger()->value;
    $i = $obj->get("global")->toBoolean()->value ? $lastIndex : 0;

    do {
      if ($i < 0 || $i > $len) {
        $obj->put("lastIndex", Runtime::$zero);
        return Runtime::$null;
      }
      // Placeholder: match() must be implemented to make exec/test work fully
      $r = $obj->match($s, $i); // XXX: write jsRegexp::match()
      $i++;
    } while ($r == null);

    $e = $r["endIndex"];
    $n = $r["length"];
    if ($obj->get("global")->toBoolean()->value == true) {
      $obj->put("lastIndex", Runtime::js_int($e));
    }
    $array = new jsArray();
    $array->put("index", Runtime::js_int($i - 1));
    $array->put("input", $str);
    $array->put("length", $n + 1);
    $array->put(0, Runtime::js_str(substr($s, $i - 1, $e - $i)));
    for ($i = 0; $i < $n; $i++) {
      $array->put($i + 1, Runtime::js_str($r[$i]));
    }
    return $array;
  }

  static function toString()
  {
    $obj = Runtime::this();
    if (!($obj instanceof jsRegexp)) {
      throw new jsException(new jsTypeError());
    }

    // Properly escape pattern for a /.../ literal: escape backslashes and the '/' delimiter
    $escaped = preg_quote($obj->pattern, '/');
    $s = '/' . $escaped . '/';

    // Append flags based on boolean values
    if ($obj->get('global')->toBoolean()->value) {
      $s .= 'g';
    }
    if ($obj->get('ignoreCase')->toBoolean()->value) {
      $s .= 'i';
    }
    if ($obj->get('multiline')->toBoolean()->value) {
      $s .= 'm';
    }

    return Runtime::js_str($s);
  }
}
