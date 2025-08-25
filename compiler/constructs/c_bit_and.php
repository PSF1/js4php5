<?php

namespace js4php5\compiler\constructs;

/**
 * Bitwise AND Operator -- Javascript '&' Operator
 */
class c_bit_and extends BaseBinaryConstruct
{
  /**
   * @param BaseConstruct $leftStatement
   * @param BaseConstruct $rightStatement
   */
  function __construct($leftStatement, $rightStatement)
  {
    // Both operands must be evaluated as values
    parent::__construct([$leftStatement, $rightStatement], true, true);
  }
}
