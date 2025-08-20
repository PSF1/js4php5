<?php

namespace js4php5\runtime;

class jsAttribute
{
  /** @var Base Underlying JS value held by this attribute */
  public $value;

  /** @var bool If true, value cannot be overwritten */
  public bool $readonly = false;

  /** @var bool If true, property is not enumerable */
  public bool $dontenum = false;

  /** @var bool If true, property cannot be deleted */
  public bool $dontdelete = false;

  /**
   * @param Base $value The JS value.
   * @param bool $ro    Read-only flag.
   * @param bool $de    DontEnum flag.
   * @param bool $dd    DontDelete flag.
   */
  function __construct($value, bool $ro = false, bool $de = false, bool $dd = false)
  {
    // Store the underlying value
    $this->value = $value;
    // Ensure flags are true booleans
    $this->readonly = (bool) $ro;
    $this->dontenum = (bool) $de;
    $this->dontdelete = (bool) $dd;
  }
}
