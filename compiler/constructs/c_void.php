<?php

namespace js4php5\compiler\constructs;

/**
 * Unary void operator -- Javascript 'void expr'
 */
class c_void extends BaseUnaryConstruct
{
  /**
   * @param BaseConstruct $expression
   */
  function __construct($expression)
  {
    // 'void' evaluates its operand (value context) and discards the result
    parent::__construct([$expression], true);
  }
}
