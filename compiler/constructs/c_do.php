<?php

namespace js4php5\compiler\constructs;

use js4php5\VarDumper;

class c_do extends BaseConstruct
{
  public $expr;
  public $statement;

  /**
   * Constructor.
   *
   * @param BaseConstruct $expr
   * @param c_block       $statement
   */
  public function __construct(BaseConstruct $expr, c_block $statement)
  {
    // Assign expression and statement constructs.
    $this->expr = $expr;
    $this->statement = $statement;
  }

  /**
   * Emit PHP source for a do-while construct.
   *
   * @param bool $unusedParameter
   * @return string
   */
  public function emit($unusedParameter = false)
  {
    // Increase nesting level used by the source emitter (indentation).
    c_source::$nest++;

    // Compose the do-while PHP snippet. Trim trailing whitespace of the emitted statement.
    $o = "do " . rtrim($this->statement->emit(true)) . " while (Runtime::js_bool(" . $this->expr->emit(true) . "));\n";

    // Restore nesting level.
    c_source::$nest--;

    return $o;
  }
}
