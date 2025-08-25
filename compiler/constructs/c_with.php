<?php

namespace js4php5\compiler\constructs;

class c_with extends BaseConstruct
{
  /** @var BaseConstruct */
  public $expr;

  /** @var BaseConstruct */
  public $statement;

  function __construct($expr, $statement)
  {
    $this->expr = $expr;
    $this->statement = $statement;
  }

  function emit($unusedParameter = false)
  {
    // Push scope with the object value, emit body, then pop scope
    $o  = "Runtime::push_scope(Runtime::js_obj(" . $this->expr->emit(true) . "));\n";
    $o .= $this->statement->emit(true);
    $o .= "Runtime::pop_scope();\n";
    return $o;
  }
}
