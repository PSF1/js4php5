<?php

namespace js4php5\compiler\constructs;

/**
 * Bitwise NOT Operator -- Javascript '~' Operator
 */
class c_bit_not extends BaseUnaryConstruct
{
  /**
   * @param BaseConstruct $expression
   */
  function __construct($expression)
  {
    // Unary operator: request value of the operand
    parent::__construct([$expression], true);
  }
}
