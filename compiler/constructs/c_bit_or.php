<?php

namespace js4php5\compiler\constructs;

/**
 * Bitwise OR Operator -- Javascript '|' Operator
 */
class c_bit_or extends BaseBinaryConstruct
{
  /**
   * @param BaseConstruct $leftStatement
   * @param BaseConstruct $rightStatement
   */
  function __construct($leftStatement, $rightStatement)
  {
    // Both operands are evaluated by value
    parent::__construct([$leftStatement, $rightStatement], true, true);
  }
}
