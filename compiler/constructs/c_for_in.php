<?php

namespace js4php5\compiler\constructs;

use js4php5\compiler\Compiler;

/**
 * for..in construct
 */
class c_for_in extends BaseConstruct
{
  /** @var BaseConstruct */
  private $one;

  /** @var BaseConstruct */
  private $list;

  /** @var BaseConstruct */
  private $statement;

  /**
   * @param BaseConstruct $one       Target (left-hand) to assign the property name
   * @param BaseConstruct $list      Source (right-hand) to iterate
   * @param BaseConstruct $statement Body to execute each iteration
   */
  function __construct($one, $list, $statement)
  {
    $this->one = $one;
    $this->list = $list;
    $this->statement = $statement;
  }

  function emit($unusedParameter = false)
  {
    $key = Compiler::generateSymbol("fv");
    // Increase nesting level; ensure restoration even if something fails
    c_source::$nest++;
    try {
      $o = "foreach (" . $this->list->emit(true) . " as \$$key) {\n";

      // If the target is a 'var' declaration node, use its special emitter
      if ($this->one instanceof c_var) {
        $v = $this->one->emit_for();
      } else {
        // Otherwise, emit the target normally (as reference)
        $v = $this->one->emit();
      }

      // Assign the current key coerced to JS string into the target
      $o .= "  Runtime::expr_assign($v, Runtime::js_str(\$$key));\n";

      // Emit the body with indentation
      $o .= "  " . trim(str_replace("\n", "\n  ", $this->statement->emit(true))) . "\n";

      // Close block and trailing newline for consistency
      $o .= "}\n";

      return $o;
    } finally {
      c_source::$nest--;
    }
  }
}
