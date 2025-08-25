<?php

namespace js4php5\compiler\constructs;

class c_strict_not_equal extends BaseBinaryConstruct
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
}
