<?php

namespace js4php5\compiler\constructs;

class c_lt extends BaseBinaryConstruct
{
  /**
   * @param BaseConstruct $leftStatement
   * @param BaseConstruct $rightStatement
   */
  function __construct($leftStatement, $rightStatement)
  {
    // Both operands evaluated by value
    parent::__construct([$leftStatement, $rightStatement], true, true);
  }

  function emit($unusedParameter = false)
  {
    // Emit using Runtime::cmp with the "<" flag (1), as per existing implementation.
    return "Runtime::cmp(" . $this->arg1->emit(true) . "," . $this->arg2->emit(true) . ", 1)";
  }
}
