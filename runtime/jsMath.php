<?php

namespace js4php5\runtime;

class jsMath extends jsObject
{
  function __construct()
  {
    parent::__construct("Math");
  }

  ////////////////////////
  // scriptable methods //
  ////////////////////////

  static function abs($x)
  {
    return Runtime::js_int(abs($x->toNumber()->value));
  }

  static function acos($x)
  {
    return Runtime::js_int(acos($x->toNumber()->value));
  }

  static function asin($x)
  {
    return Runtime::js_int(asin($x->toNumber()->value));
  }

  static function atan($x)
  {
    return Runtime::js_int(atan($x->toNumber()->value));
  }

  static function atan2($y, $x)
  {
    return Runtime::js_int(atan2($y->toNumber()->value, $x->toNumber()->value));
  }

  static function ceil($x)
  {
    return Runtime::js_int(ceil($x->toNumber()->value));
  }

  static function cos($x)
  {
    return Runtime::js_int(cos($x->toNumber()->value));
  }

  static function exp($x)
  {
    return Runtime::js_int(exp($x->toNumber()->value));
  }

  static function floor($x)
  {
    return Runtime::js_int(floor($x->toNumber()->value));
  }

  static function log($x)
  {
    return Runtime::js_int(log($x->toNumber()->value));
  }

  static function max($v1, $v2)
  {
    // Determine actual argument count from activation's "arguments.length"
    $actualLen = (int) Runtime::$contexts[0]->var->get('arguments')->get('length')->toNumber()->value;

    if ($actualLen === 0) {
      // JS: Math.max() with no args -> -Infinity
      return Runtime::expr_u_minus(Runtime::$infinity);
    }

    // Only consider real arguments; ignore synthesized undefined beyond actual length
    $args = array_slice(func_get_args(), 0, $actualLen);
    $arr = array();

    foreach ($args as $arg) {
      $v = $arg->toNumber()->value;
      if (is_nan($v)) {
        return Runtime::$nan;
      }
      $arr[] = $v;
    }

    return Runtime::js_int(max($arr));
  }

  static function min($v1, $v2)
  {
    // Determine actual argument count from activation's "arguments.length"
    $actualLen = (int) Runtime::$contexts[0]->var->get('arguments')->get('length')->toNumber()->value;

    if ($actualLen === 0) {
      // JS: Math.min() with no args -> +Infinity
      return Runtime::$infinity;
    }

    // Only consider real arguments; ignore synthesized undefined beyond actual length
    $args = array_slice(func_get_args(), 0, $actualLen);
    $arr = array();

    foreach ($args as $arg) {
      $v = $arg->toNumber()->value;
      if (is_nan($v)) {
        return Runtime::$nan;
      }
      $arr[] = $v;
    }

    return Runtime::js_int(min($arr));
  }

  static function pow($x, $y)
  {
    return Runtime::js_int(pow($x->toNumber()->value, $y->toNumber()->value));
  }

  static function random()
  {
    // JS: 0 <= x < 1 (never equals 1)
    $r = mt_rand() / (mt_getrandmax() + 1);
    return Runtime::js_int($r);
  }

  static function round($x)
  {
    return Runtime::js_int(round($x->toNumber()->value));
  }

  static function sin($x)
  {
    return Runtime::js_int(sin($x->toNumber()->value));
  }

  static function sqrt($x)
  {
    return Runtime::js_int(sqrt($x->toNumber()->value));
  }

  static function tan($x)
  {
    return Runtime::js_int(tan($x->toNumber()->value));
  }
}
