<?php

namespace js4php5\compiler\constructs;

class c_if extends BaseConstruct
{
  /** @var BaseConstruct */
  public $cond;

  /** @var BaseConstruct */
  public $ifblock;

  /** @var BaseConstruct|null */
  public $elseblock;

  /**
   * Constructor.
   *
   * @param BaseConstruct      $cond      Condition.
   * @param BaseConstruct      $ifblock   If block.
   * @param BaseConstruct|null $elseblock Else block (optional).
   */
  function __construct($cond, $ifblock, $elseblock = null)
  {
    // Normalize and store to avoid dynamic properties (PHP 8.2)
    $this->cond      = $cond;
    $this->ifblock   = $ifblock;
    $this->elseblock = $elseblock;
  }

  function emit($unusedParameter = false)
  {
    // Emit condition by value, and the if block as-is
    $o = "if (Runtime::js_bool(" . $this->cond->emit(true) . ")) " . $this->ifblock->emit(true);

    // If there is an else block and it is not a c_nop, append " else <block>" with a single trailing newline
    if ($this->elseblock && !$this->elseblock instanceof c_nop) {
      // Trim trailing newlines from both blocks to avoid double blank lines
      $o = rtrim($o) . " else " . rtrim($this->elseblock->emit(true)) . "\n";
    }

    return $o;
  }
}
