<?php

namespace js4php5\compiler\constructs;

class c_literal_number extends BaseConstruct
{
  /** @var int|float|string Numeric literal as parsed (kept as-is for emission) */
  public $v;

  /**
   * @param int|float|string $v
   */
  function __construct($v)
  {
    // Store the literal as provided to avoid altering formats like hex or scientific notation
    $this->v = $v;
  }

  function emit($unusedParameter = false)
  {
    return "Runtime::js_int(" . $this->v . ")";
  }
}
