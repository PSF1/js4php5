<?php

namespace js4php5\compiler\constructs;

use js4php5\VarDumper;

class c_for extends BaseConstruct
{
  public $init;
  public $condition;
  public $increment;
  public $statement;

  /**
   * @param c_var|c_assign $init
   * @param BaseBinaryConstruct|c_call $condition
   * @param BaseConstruct $increment
   * @param c_block|c_statement $statement
   */
  public function __construct($init, $condition, $increment, $statement)
  {
    // Assign the parts of the for construct: initialization, condition, increment and the body.
    $this->init = $init;
    $this->condition = $condition;
    $this->increment = $increment;
    $this->statement = $statement;
  }

  /**
   * Emit PHP source for a JavaScript for(...) construct.
   *
   * @param bool $unusedParameter
   * @return string
   */
  public function emit($unusedParameter = false)
  {
    // Start with any initialization code (may already include its own punctuation).
    $o = $this->init ? $this->init->emit(true) : '';

    // Increase nesting level used by the source emitter (indentation).
    c_source::$nest++;

    // Build the for( ; condition ; increment ) header.
    $o .= "for (;" . ($this->condition ? "Runtime::js_bool(" . $this->condition->emit(true) . ")" : '');
    $o .= ";" . ($this->increment ? $this->increment->emit(true) : '') . ") {\n";

    // Append the emitted statement/block.
    $o .= $this->statement->emit(true);

    // Close the for block.
    $o .= "\n}\n";

    // Restore nesting level.
    c_source::$nest--;

    return $o;
  }
}
