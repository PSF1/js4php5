<?php

namespace js4php5\runtime;

use Exception;

class jsException extends Exception
{
  /** @var int Custom type flag used by the runtime to mark thrown exceptions */
  const EXCEPTION = 7;

  /** @var int Custom type flag (unused in current code, kept for compat) */
  const NORMAL = 8;

  /** @var int One of the custom flags (EXCEPTION/NORMAL) */
  public $type;

  /** @var Base Underlying JS value carried by the exception */
  public $value;

  /**
   * Wrap a JS value into a runtime exception.
   *
   * @param Base $value The underlying JS value associated with the exception.
   */
  function __construct($value)
  {
    // Keep the base Exception with default message/code
    parent::__construct();
    $this->type = self::EXCEPTION;
    $this->value = $value;
  }
}
