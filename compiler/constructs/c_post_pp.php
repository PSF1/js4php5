<?php

namespace js4php5\compiler\constructs;

/**
 * Construct to emulate JavaScript post-increment operator (var++)
 */
class c_post_pp extends BaseUnaryConstruct
{
  /**
   * @param BaseConstruct $identifier
   */
  function __construct($identifier)
  {
    // Post-increment needs a reference to the operand, not its value
    parent::__construct([$identifier], false);
  }
}
