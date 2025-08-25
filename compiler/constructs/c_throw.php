<?php

namespace js4php5\compiler\constructs;

class c_throw extends BaseConstruct
{
  /** @var BaseConstruct */
  public $expr;

  /**
   * @param BaseConstruct $expr
   */
  function __construct($expr)
  {
    $this->expr = $expr;
  }

  function emit($unusedParameter = false)
  {
    // Emit a fully-qualified runtime exception to work from the compiled script namespace.
    return "throw new \\js4php5\\runtime\\jsException(" . $this->expr->emit(true) . ");\n";
  }
}
