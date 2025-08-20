<?php

namespace js4php5\runtime;

use js4php5\VarDumper;

class jsRefNull extends jsRef
{
  /**
   * Reference to a non-existent identifier/property.
   * In non-strict mode, assigning to it creates a global property.
   */
  function __construct($propName)
  {
    parent::__construct(null, $propName);
  }

  /**
   * Reading from an unresolved reference throws a ReferenceError wrapped in jsException.
   *
   * @throws jsException
   */
  function getValue()
  {
    // Throw a ReferenceError with a brief dump for context
    throw new jsException(new jsReferenceError(VarDumper::dumpAsString($this)));
  }

  /**
   * Writing to an unresolved reference creates a property on the global object.
   * Returns according to $ret, mirroring jsRef::putValue behavior:
   * - ret=0: null
   * - ret=1: written value
   * - ret=2: previous value (if any), before the write
   */
  function putValue($w, $ret = 0)
  {
    $key = (string)$this->propName;

    // Previous value if requested
    $prev = null;
    if ($ret == 2) {
      // If property existed on global, fetch it; otherwise null
      if (Runtime::$global->hasProperty($key)) {
        $prev = Runtime::$global->get($key);
      }
    }

    // Perform the write on the global object
    Runtime::$global->put($key, $w);

    // Return according to requested mode
    if ($ret == 1) {
      return $w;
    }
    return $prev;
  }
}
