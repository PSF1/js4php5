<?php

namespace js4php5\compiler\constructs;

/**
 * Construct to emulate JavaScript post-decrement operator (var--)
 */
class c_post_mm extends BaseUnaryConstruct
{
  /**
   * @param BaseConstruct $identifier Identifier to be decremented (must be a reference)
   */
  function __construct($identifier)
  {
    // Post-decrement needs a reference to the operand, not its value
    // So we pass getValue=false explicitly.
    parent::__construct([$identifier], false);
  }
}
