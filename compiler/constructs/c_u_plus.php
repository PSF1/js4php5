<?php

namespace js4php5\compiler\constructs;

/**
 * Unary plus operator -- Javascript '+expr'
 */
class c_u_plus extends BaseUnaryConstruct
{
  /**
   * @param BaseConstruct $expression
   */
  function __construct($expression)
  {
    // Unary plus needs the value of the operand
    parent::__construct([$expression], true);
  }
}
