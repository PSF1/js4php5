<?php

namespace js4php5\compiler\constructs;

/**
 * Construct to emulate JavaScript pre-decrement operator (--var)
 */
class c_pre_mm extends BaseUnaryConstruct
{
  /**
   * @param BaseConstruct $identifier Identifier to be decremented (must be a reference)
   */
  function __construct($identifier)
  {
    // Pre-decrement needs a reference to the operand, not its value
    parent::__construct([$identifier], false);
  }
}
