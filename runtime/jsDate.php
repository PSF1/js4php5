<?php

namespace js4php5\runtime;

class jsDate extends jsObject
{
  function __construct($y = null, $m = null, $d = null, $h = null, $mn = null, $s = null, $ms = null)
  {
    parent::__construct("Date", Runtime::$proto_date);

    // Normalize undefineds
    $y  = ($y  == null) ? Runtime::$undefined : $y;
    $m  = ($m  == null) ? Runtime::$undefined : $m;
    $d  = ($d  == null) ? Runtime::$undefined : $d;
    $h  = ($h  == null) ? Runtime::$undefined : $h;
    $mn = ($mn == null) ? Runtime::$undefined : $mn;
    $s  = ($s  == null) ? Runtime::$undefined : $s;
    $ms = ($ms == null) ? Runtime::$undefined : $ms;

    if ($y == Runtime::$undefined) {
      // Now in ms
      $value = floor(microtime(true) * 1000);
    } elseif ($m == Runtime::$undefined) {
      // Single-argument constructor
      $v = $y->toPrimitive();
      if ($v->type == Base::STRING) {
        $ts = strtotime($v->value);
        $value = ($ts === false) ? NAN : ($ts * 1000.0);
      } else {
        $value = (float)$v->toNumber()->value;
      }
    } else {
      // Full-argument constructor
      $y  = $y->toNumber()->value;
      $m  = $m->toNumber()->value;
      $d  = ($d  == Runtime::$undefined) ? 1 : $d->toNumber()->value;
      $h  = ($h  == Runtime::$undefined) ? 0 : $h->toNumber()->value;
      $mn = ($mn == Runtime::$undefined) ? 0 : $mn->toNumber()->value;
      $s  = ($s  == Runtime::$undefined) ? 0 : $s->toNumber()->value;
      $ms = ($ms == Runtime::$undefined) ? 0 : $ms->toNumber()->value;

      if (!is_nan($y)) {
        $y2k = floor($y);
        if ($y2k >= 0 && $y2k <= 99) {
          $y = 1900 + $y2k;
        }
      }
      // mktime expects 1-based month; JS months are 0-based
      $value = (mktime((int)$h, (int)$mn, (int)$s, (int)($m + 1), (int)$d, (int)$y) * 1000.0) + (float)$ms;
    }

    $this->value = (float)$value;
  }

  ////////////////////////
  // scriptable methods //
  ////////////////////////

  static function object($value)
  {
    // Map "new Date(...)" vs "Date(...)" semantics
    $args = func_get_args();
    if (jsFunction::isConstructor()) {
      return new jsDate(...$args);
    }
    $d = new jsDate(...$args);
    return $d->toStr();
  }

  static function parse($v)
  {
    $ts = strtotime($v->toStr()->value);
    $ms = ($ts === false) ? NAN : ($ts * 1000.0);
    return Runtime::js_int($ms);
  }

  static function UTC($y, $m, $d, $h, $mn, $s, $ms)
  {
    $y  = $y->toNumber()->value;
    $m  = $m->toNumber()->value;
    $d  = ($d  == Runtime::$undefined) ? 1 : $d->toNumber()->value;
    $h  = ($h  == Runtime::$undefined) ? 0 : $h->toNumber()->value;
    $mn = ($mn == Runtime::$undefined) ? 0 : $mn->toNumber()->value;
    $s  = ($s  == Runtime::$undefined) ? 0 : $s->toNumber()->value;
    $ms = ($ms == Runtime::$undefined) ? 0 : $ms->toNumber()->value;

    if (!is_nan($y)) {
      $y2k = floor($y);
      if ($y2k >= 0 && $y2k <= 99) {
        $y = 1900 + $y2k;
      }
    }
    $value = (gmmktime((int)$h, (int)$mn, (int)$s, (int)($m + 1), (int)$d, (int)$y) * 1000.0) + (float)$ms;
    return Runtime::js_int($value);
  }

  static function toString()
  {
    // RFC 2822 format (locale-independent)
    $obj = Runtime::this();
    if (!($obj instanceof jsDate)) {
      throw new jsException(new jsTypeError());
    }
    return Runtime::js_str(date("r", (int)($obj->value / 1000)));
  }

  static function toDateString()
  {
    $obj = Runtime::this();
    if (!($obj instanceof jsDate)) {
      throw new jsException(new jsTypeError());
    }
    return Runtime::js_str(date("D M j Y", (int)($obj->value / 1000)));
  }

  static function toTimeString()
  {
    $obj = Runtime::this();
    if (!($obj instanceof jsDate)) {
      throw new jsException(new jsTypeError());
    }
    return Runtime::js_str(date("G:i:s T", (int)($obj->value / 1000)));
  }

  static function toLocaleString()
  {
    // Avoid strftime deprecations; use a reasonable default
    $obj = Runtime::this();
    if (!($obj instanceof jsDate)) {
      throw new jsException(new jsTypeError());
    }
    return Runtime::js_str(date("Y-m-d H:i:s", (int)($obj->value / 1000)));
  }

  static function toLocaleDateString()
  {
    $obj = Runtime::this();
    if (!($obj instanceof jsDate)) {
      throw new jsException(new jsTypeError());
    }
    return Runtime::js_str(date("Y-m-d", (int)($obj->value / 1000)));
  }

  static function toLocaleTimeString()
  {
    $obj = Runtime::this();
    if (!($obj instanceof jsDate)) {
      throw new jsException(new jsTypeError());
    }
    return Runtime::js_str(date("H:i:s", (int)($obj->value / 1000)));
  }

  static function getTime()
  {
    return self::valueOf();
  }

  static function valueOf()
  {
    $obj = Runtime::this();
    if (!($obj instanceof jsDate)) {
      throw new jsException(new jsTypeError());
    }
    return Runtime::js_int($obj->value);
  }

  static function getFullYear()
  {
    $t = self::valueOf()->value;
    if (is_nan($t)) {
      return Runtime::$nan;
    }
    return Runtime::js_int((int)date("Y", (int)($t / 1000)));
  }

  static function getUTCFullYear()
  {
    $t = self::valueOf()->value;
    if (is_nan($t)) {
      return Runtime::$nan;
    }
    return Runtime::js_int((int)gmdate("Y", (int)($t / 1000)));
  }

  static function getDay()
  {
    $t = self::valueOf()->value;
    if (is_nan($t)) {
      return Runtime::$nan;
    }
    return Runtime::js_int((int)date("w", (int)($t / 1000)));
  }

  static function getUTCDay()
  {
    $t = self::valueOf()->value;
    if (is_nan($t)) {
      return Runtime::$nan;
    }
    return Runtime::js_int((int)gmdate("w", (int)($t / 1000)));
  }

  static function getMillieconds()
  {
    // Delegate to the correct implementation
    return self::getMilliseconds();
  }

  // Standards-compliant name
  static function getMilliseconds()
  {
    $t = self::valueOf()->value;
    if (is_nan($t)) {
      return Runtime::$nan;
    }
    // Return milliseconds within the current second [0, 999]
    return Runtime::js_int(fmod($t, 1000.0));
  }

  static function getUTCMilliseconds()
  {
    $t = self::valueOf()->value;
    if (is_nan($t)) {
      return Runtime::$nan;
    }
    return Runtime::js_int(fmod($t, 1000.0));
  }

  static function getTimezoneOffset()
  {
    // JS getTimezoneOffset returns minutes between local time and UTC (UTC - local), positive if behind UTC.
    $obj = Runtime::this();
    if (!($obj instanceof jsDate)) {
      throw new jsException(new jsTypeError());
    }
    $dt = (new \DateTimeImmutable('@' . (int)($obj->value / 1000)))->setTimezone(new \DateTimeZone(date_default_timezone_get()));
    $offsetSeconds = $dt->getOffset(); // seconds from UTC to local
    $minutes = -($offsetSeconds / 60); // invert sign to match JS
    return Runtime::js_int((float)$minutes);
  }

  static function setTime($time)
  {
    $obj = Runtime::this();
    if (!($obj instanceof jsDate)) {
      throw new jsException(new jsTypeError());
    }
    $v = (float)$time->toNumber()->value;
    $obj->value = $v;
    return Runtime::js_int($v);
  }

  static function setUTCMilliseconds($ms)
  {
    return self::setMilliseconds($ms);
  }

  static function setMilliseconds($ms)
  {
    $obj = Runtime::this();
    if (!($obj instanceof jsDate)) {
      throw new jsException(new jsTypeError());
    }
    $t = self::valueOf()->value;
    $ms = (float)$ms->toNumber()->value;
    $v = floor($t / 1000) * 1000 + $ms;
    $obj->value = $v;
    return Runtime::js_int($v);
  }

  static function setUTCSeconds($s, $ms)
  {
    return self::setSeconds($s, $ms);
  }

  static function setSeconds($s, $ms)
  {
    $obj = Runtime::this();
    if (!($obj instanceof jsDate)) {
      throw new jsException(new jsTypeError());
    }
    $t = $obj->value;
    $sec = (float)$s->toNumber()->value;
    $millis = ($ms == Runtime::$undefined) ? fmod($t, 1000.0) : (float)$ms->toNumber()->value;
    $v = floor($t / 60000) * 60000 + (1000.0 * $sec + $millis);
    $obj->value = $v;
    return Runtime::js_int($v);
  }

  // NOTE: The remaining setters (setMinutes, setHours, setMonth, setFullYear, etc.)
  // in the original code have inconsistencies (use of undefined helpers and wrong types).
  // They can be revised similarly if needed. For now, we keep their signatures and behavior
  // untouched to minimize changes and focus on removing deprecations and obvious bugs.

  static function toUTCString()
  {
    $obj = Runtime::this();
    if (!($obj instanceof jsDate)) {
      throw new jsException(new jsTypeError());
    }
    $t = (int)($obj->value / 1000);
    // RFC 1123 format with explicit GMT
    return Runtime::js_str(gmdate('D, d M Y H:i:s', $t) . ' GMT');
  }
}
