<?php

namespace js4php5\compiler\constructs;

class c_while extends BaseConstruct
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
    // Increase nesting and ensure restoration (even if an error occurs while emitting)
    c_source::$nest++;
    try {
      $o = "while (Runtime::js_bool(" . $this->expr->emit(true) . ")) " . $this->statement->emit(true) . "\n";
      return $o;
    } finally {
      c_source::$nest--;
    }
  }
}
