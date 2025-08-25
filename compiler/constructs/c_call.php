<?php

namespace js4php5\compiler\constructs;

class c_call extends BaseConstruct
{
  /** @var BaseConstruct */
  public $expr;

  /** @var BaseConstruct[] */
  public $args;

  /**
   * @param BaseConstruct   $expr
   * @param BaseConstruct[]|BaseConstruct $args
   */
  function __construct($expr, $args)
  {
    $this->expr = $expr;
    // Normalize to array of nodes: wrap single node instead of casting
    $this->args = is_array($args) ? $args : [$args];
  }

  function emit($unusedParameter = false)
  {
    $args = array();
    /** @var BaseConstruct $arg */
    foreach ($this->args as $arg) {
      $args[] = $arg->emit(true);
    }
    return "Runtime::call(" . $this->expr->emit() . ", array(" . implode(",", $args) . "))";
  }
}
