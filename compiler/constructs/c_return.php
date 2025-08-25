<?php

namespace js4php5\compiler\constructs;

class c_return extends BaseConstruct
{
  /** @var BaseConstruct|string|null */
  public $expr;

  /**
   * @param BaseConstruct|string|null $expr Expression node or ';' to indicate "return;" without expression.
   */
  function __construct($expr)
  {
    $this->expr = $expr;
  }

  function emit($unusedParameter = false)
  {
    // Allow returning values to the PHP caller: if there is no expression, we return undefined.
    if ($this->expr === ';' || $this->expr === null) {
      return "return Runtime::\$undefined;\n";
    }

    // Return con expresión: emit the value of the expression
    return "return " . $this->expr->emit(true) . ";\n";
  }
}
