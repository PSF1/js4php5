<?php

namespace js4php5\runtime;

class jsArray extends jsObject
{
  protected $length;

  function __construct($len = 0, $args = array())
  {
    parent::__construct("Array", Runtime::$proto_array);

    // Always store length as a JS number (Base::NUMBER)
    if ($len instanceof Base) {
      $this->length = $len;
    } else {
      $this->length = Runtime::js_int((float)$len);
    }

    // Initialize elements if provided
    foreach ($args as $index => $value) {
      // Avoid noisy output (was echo during development)
      $this->put($index, $value);
    }
  }

  function put($name, $value, $opts = null)
  {
    $name = (string)$name;

    if ($name === "length") {
      // When setting length, if new length < old length, truncate
      // Normalize to UInt32 as in JS
      $newLen = $value instanceof Base ? (int)$value->toUInt32()->value : (int)$value;
      $oldLen = (int)$this->length->toUInt32()->value;

      if ($newLen < $oldLen && is_array($this->slots)) {
        foreach (array_keys($this->slots) as $index) {
          if (is_numeric($index) && (int)$index >= $newLen) {
            $this->delete($index);
          }
        }
      }

      $this->length = Runtime::js_int((float)$newLen);
      return;
    }

    // Regular element/property assignment
    if (is_numeric($name)) {
      $idx = (int)$name;
      $curLen = (int)$this->length->toUInt32()->value;
      if ($idx >= $curLen) {
        $this->length = Runtime::js_int((float)($idx + 1));
      }
    }

    return parent::put($name, $value, $opts);
  }

  static public function object($value)
  {
    // new Array(len)
    if (func_num_args() == 1 && $value->type == Base::NUMBER && $value->toUInt32()->value == $value->value) {
      $obj = new jsArray();
      $obj->put("length", $value);
      return $obj;
    }
    // new Array(...items)
    $contrived = func_get_args();
    return call_user_func_array([Runtime::class, "literal_array"], $contrived);
  }

  static public function toLocaleString()
  {
    // TODO: Implement proper locale-specific formatting
    return jsArray::toString();
  }

  static public function toString()
  {
    $obj = Runtime::this();
    if (!($obj instanceof jsArray)) {
      throw new jsException(new jsTypeError());
    }
    return $obj->defaultValue();
  }

  static public function concat()
  {
    $array = new jsArray();
    $args = func_get_args();
    // Start with "this" array
    array_unshift($args, Runtime::this());

    while (count($args) > 0) {
      $obj = array_shift($args);
      if (!($obj instanceof jsArray)) {
        $array->_push($obj);
      } else {
        $len = (int)$obj->get("length")->toUInt32()->value;
        for ($k = 0; $k < $len; $k++) {
          if ($obj->hasProperty($k)) {
            $array->_push($obj->get($k));
          }
        }
      }
    }
    return $array;
  }

  ////////////////////////
  // scriptable methods //
  ////////////////////////

  function _push($val)
  {
    // Use current numeric length for next index
    $v = (int)$this->length->toUInt32()->value;
    $this->put($v, $val);
    $this->length = Runtime::js_int((float)($v + 1));
  }

  static public function join($sep)
  {
    $obj = Runtime::this();
    $len = (int)$obj->get("length")->toUInt32()->value;

    $separator = ($sep == Runtime::$undefined) ? "," : $sep->toStr()->value;

    if ($len === 0) {
      return Runtime::js_str("");
    }

    $arr = jsArray::toNativeArray($obj);
    $arr2 = array();
    foreach ($arr as $elem) {
      // Convert each element to string
      $arr2[] = $elem->toStr()->value;
    }
    return Runtime::js_str(implode($separator, $arr2));
  }

  static function toNativeArray($obj)
  {
    $len = (int)$obj->get("length")->toUInt32()->value;
    $arr = array();
    for ($i = 0; $i < $len; $i++) {
      $arr[$i] = $obj->get($i);
    }
    return $arr;
  }

  static public function pop()
  {
    $obj = Runtime::this();
    $len = (int)$obj->get("length")->toUInt32()->value;

    if ($len === 0) {
      $obj->put("length", Runtime::js_int(0));
      return Runtime::$undefined;
    }

    $index = $len - 1;
    $elem = $obj->get($index);
    $obj->delete($index);
    $obj->put("length", Runtime::js_int((float)$index));
    return $elem;
  }

  static public function push()
  {
    $obj = Runtime::this();
    $n = (int)$obj->get("length")->toUInt32()->value;
    $args = func_get_args();

    while (count($args) > 0) {
      $arg = array_shift($args);
      $obj->put($n, $arg);
      $n++;
    }

    $obj->put("length", Runtime::js_int((float)$n));
    return Runtime::js_int((float)$n);
  }

  static public function reverse()
  {
    $obj = Runtime::this();
    $len = (int)$obj->get("length")->toUInt32()->value;
    $mid = (int)floor($len / 2);
    $k = 0;
    while ($k != $mid) {
      $l = $len - $k - 1;
      if (!$obj->hasProperty($k)) {
        if (!$obj->hasProperty($l)) {
          $obj->delete($k);
          $obj->delete($l);
        } else {
          $obj->put($k, $obj->get($l));
          $obj->delete($l);
        }
      } else {
        if (!$obj->hasProperty($l)) {
          $obj->put($l, $obj->get($k));
          $obj->delete($k);
        } else {
          $a = $obj->get($k);
          $obj->put($k, $obj->get($l));
          $obj->put($l, $a);
        }
      }
      $k++;
    }
    return $obj;
  }

  static public function shift()
  {
    $obj = Runtime::this();
    $len = (int)$obj->get("length")->toUInt32()->value;

    if ($len === 0) {
      $obj->put("length", Runtime::js_int(0));
      return Runtime::$undefined;
    }

    $first = $obj->get(0);
    $k = 1;
    while ($k != $len) {
      if ($obj->hasProperty($k)) {
        $obj->put($k - 1, $obj->get($k));
      } else {
        $obj->delete($k - 1);
      }
      $k++;
    }
    $obj->delete($len - 1);
    $obj->put("length", Runtime::js_int((float)($len - 1)));
    return $first;
  }

  static public function slice($start, $end)
  {
    $obj = Runtime::this();
    $array = new jsArray();
    $len = (int)$obj->get("length")->toUInt32()->value;

    $startVal = (int)$start->toInteger()->value;
    $k = ($startVal < 0) ? max($len + $startVal, 0) : min($len, $startVal);

    if ($end == Runtime::$undefined) {
      $endVal = $len;
    } else {
      $endVal = (int)$end->toInteger()->value;
    }

    $endVal = ($endVal < 0) ? max($len + $endVal, 0) : min($len, $endVal);

    $n = 0;
    while ($k < $endVal) {
      if ($obj->hasProperty($k)) {
        $array->put($n, $obj->get($k));
      }
      $k++;
      $n++;
    }
    $array->put("length", Runtime::js_int((float)$n));
    return $array;
  }

  static public function sort($comparefn)
  {
    $obj = Runtime::this();
    $arr = jsArray::toNativeArray($obj);

    Runtime::$sortfn = $comparefn;
    // Use the helper in this class
    usort($arr, [self::class, "sort_helper"]);
    Runtime::$sortfn = null;

    $len = count($arr);
    for ($i = 0; $i < $len; $i++) {
      $obj->put($i, $arr[$i]);
    }
    $obj->put('length', Runtime::js_int((float)$len));
    return $obj;
  }

  static public function sort_helper($a, $b)
  {
    if ($a == Runtime::$undefined) {
      return ($b == Runtime::$undefined) ? 0 : 1;
    }
    if ($b == Runtime::$undefined) {
      return -1;
    }

    if (Runtime::$sortfn == null || Runtime::$sortfn == Runtime::$undefined) {
      // Default: string comparison semantics
      $as = $a->toStr();
      $bs = $b->toStr();
      if (Runtime::js_bool(Runtime::expr_lt($as, $bs))) {
        return -1;
      }
      if (Runtime::js_bool(Runtime::expr_gt($as, $bs))) {
        return 1;
      }
      return 0;
    }

    // Custom comparator: call with (a, b)
    $cmp = Runtime::$sortfn->_call(Runtime::$global, [$a, $b])->toInteger()->value;
    // Ensure PHP int return (-1, 0, 1 typically)
    return (int)$cmp;
  }

  static public function splice($start, $deleteCount)
  {
    $obj = Runtime::this();
    $args = func_get_args();
    array_shift($args); // remove $start
    array_shift($args); // remove $deleteCount

    $array = new jsArray();
    $len = (int)$obj->get("length")->toUInt32()->value;

    $startVal = (int)$start->toInteger()->value;
    $startIdx = ($startVal < 0) ? max($len + $startVal, 0) : min($len, $startVal);

    $del = (int)min(max((int)$deleteCount->toInteger()->value, 0), $len - $startIdx);

    // Collect removed items
    for ($k = 0; $k < $del; $k++) {
      if ($obj->hasProperty($startIdx + $k)) {
        $array->put($k, $obj->get($startIdx + $k));
      }
    }
    $array->put("length", Runtime::js_int((float)$del));

    $nbitems = count($args);

    // Shift elements to make room or close gaps
    if ($nbitems != $del) {
      if ($nbitems <= $del) {
        $k = $startIdx;
        while ($k != $len - $del) {
          $r22 = $k + $del;
          $r23 = $k + $nbitems;
          if ($obj->hasProperty($r22)) {
            $obj->put($r23, $obj->get($r22));
          } else {
            $obj->delete($r23);
          }
          $k++;
        }
        $k = $len;
        while ($k != $len - $del + $nbitems) {
          $obj->delete($k - 1);
          $k--;
        }
      } else {
        $k = $len - $del;
        while ($k != $startIdx) {
          $r39 = $k + $del - 1;
          $r40 = $k + $nbitems - 1;
          if ($obj->hasProperty($r39)) {
            $obj->put($r40, $obj->get($r39));
          } else {
            $obj->delete($r40);
          }
          $k--;
        }
      }
    }

    // Insert new items
    $k = $startIdx;
    while (count($args) > 0) {
      $obj->put($k++, array_shift($args));
    }

    $obj->put("length", Runtime::js_int((float)($len - $del + $nbitems)));
    return $array;
  }

  static public function unshift()
  {
    $obj = Runtime::this();
    $len = (int)$obj->get("length")->toUInt32()->value;
    $args = func_get_args();
    $nbitems = count($args);

    $k = $len;
    while ($k != 0) {
      if ($obj->hasProperty($k - 1)) {
        $obj->put($k + $nbitems - 1, $obj->get($k - 1));
      } else {
        $obj->delete($k + $nbitems - 1);
      }
      $k--;
    }
    $k = 0;
    while (count($args) > 0) {
      $obj->put($k, array_shift($args));
      $k++;
    }
    $obj->put("length", Runtime::js_int((float)($len + $nbitems)));
    return Runtime::js_int((float)($len + $nbitems));
  }

  function defaultValue($iggy = null)
  {
    $arr = array();
    $lengthVal = (int)$this->length->toUInt32()->value;
    for ($i = 0; $i < $lengthVal; $i++) {
      $arr[$i] = '';
    }
    if (is_array($this->slots)) {
      foreach ($this->slots as $index => $value) {
        if (is_numeric($index)) {
          $arr[(int)$index] = $value->value->toStr()->value;
        }
      }
    }
    $o = implode(",", $arr);
    return Runtime::js_str($o);
  }

  function get($name)
  {
    $name = (string)$name;
    if ($name === "length") {
      return $this->length;
    }
    return parent::get($name);
  }
}
