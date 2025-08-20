<?php

namespace js4php5\runtime;

class jsRef
{
  /** @var string Reference type marker (Base::REF) */
  public $type;

  /** @var jsObject Base object holding the property */
  public $base;

  /** @var string|int Property name or index */
  public $propName;

  /**
   * Create a reference to a property on a base object.
   *
   * @param jsObject     $base     The base object.
   * @param string|int   $propName The property name or index.
   */
  function __construct($base, $propName)
  {
    $this->type = Base::REF;
    $this->base = $base;
    $this->propName = $propName;
  }

  /**
   * Get the current value of the referenced property.
   */
  function getValue()
  {
    if (!is_object($this->base)) {
      // Debug aid: this should not happen in normal execution
      echo "<pre>";
      debug_print_backtrace();
      echo "</pre>";
    }
    // Cast property name defensively; jsObject/jsArray also cast internally
    return $this->base->get((string)$this->propName);
  }

  /**
   * Write a value into the referenced property.
   *
   * @param Base $w   The value to write.
   * @param int  $ret Controls the return value:
   *                  0 -> return null (default behavior),
   *                  1 -> return the written value,
   *                  2 -> return the previous value.
   */
  function putValue($w, $ret = 0)
  {
    $v = null;

    // If caller wants the previous value, fetch it before writing
    if ($ret == 2) {
      $v = $this->base->get((string)$this->propName);
    }

    // Perform the write
    $this->base->put((string)$this->propName, $w);

    // Return according to requested mode
    if ($ret == 1) {
      return $w;
    }
    return $v; // null by default, or previous value when ret=2
  }
}
