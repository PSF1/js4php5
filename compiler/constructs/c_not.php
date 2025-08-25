<?php

namespace js4php5\compiler\constructs;

/**
 * Logical NOT operator -- Javascript '!' operator
 */
class c_not extends BaseUnaryConstruct
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
