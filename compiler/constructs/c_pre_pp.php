<?php

namespace js4php5\compiler\constructs;

/**
 * Construct to emulate JavaScript pre-increment operator (++var)
 */
class c_pre_pp extends BaseUnaryConstruct
{
  /**
   * @param BaseConstruct $identifier Identifier to be incremented (must be a reference)
   */
  function __construct($identifier)
  {
    // Pre-increment needs a reference to the operand, not its value.
    parent::__construct([$identifier], false);
  }
}
