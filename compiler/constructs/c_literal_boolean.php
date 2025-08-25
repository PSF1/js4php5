<?php

namespace js4php5\compiler\constructs;

class c_literal_boolean extends BaseConstruct
{
  /** @var bool */
  public $v;

  /**
   * @param mixed $v
   */
  function __construct($v)
  {
    // Normalize to real boolean to avoid dynamic properties and keep consistency
    $this->v = (bool) $v;
  }

  function emit($unusedParameter = false)
  {
    return $this->v ? 'Runtime::$true' : 'Runtime::$false';
  }
}
