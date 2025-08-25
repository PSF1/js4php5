<?php

namespace js4php5\compiler\constructs;

class c_typeof extends BaseUnaryConstruct
{
  /**
   * @param BaseConstruct $identifier
   */
  function __construct($identifier)
  {
    // typeof needs the value of the operand
    parent::__construct([$identifier], true);
  }
}
