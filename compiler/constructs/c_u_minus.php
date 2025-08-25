<?php

namespace js4php5\compiler\constructs;

/**
 * Unary minus operator -- Javascript '-expr'
 */
class c_u_minus extends BaseUnaryConstruct
{
  /**
   * @param BaseConstruct $expression
   */
  function __construct($expression)
  {
    // Unary minus needs the value of the operand
    parent::__construct([$expression], true);
  }
}
