<?php

namespace js4php5\compiler\constructs;

class c_comma extends BaseBinaryConstruct
{
  /**
   * @param BaseConstruct $leftStatement
   * @param BaseConstruct $rightStatement
   */
  function __construct($leftStatement, $rightStatement)
  {
    // Evaluate both operands by value; result is the right-hand side (handled by Runtime::expr_comma)
    parent::__construct([$leftStatement, $rightStatement], true, true);
  }
}
