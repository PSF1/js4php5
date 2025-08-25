<?php

namespace js4php5\compiler\constructs;

use js4php5\compiler\Compiler;

/**
 * Logical OR Construct - Javascript '||' operator
 */
class c_or extends BaseBinaryConstruct
{
  /**
   * @param BaseConstruct $leftStatement
   * @param BaseConstruct $rightStatement
   */
  function __construct($leftStatement, $rightStatement)
  {
    // Both operands will be evaluated by value in emit() (short-circuit pattern below),
    // keep flags consistent with that behavior.
    parent::__construct([$leftStatement, $rightStatement], true, true);
  }

  /**
   * @param bool $unusedParameter
   *
   * @return string PHP code chunk
   */
  function emit($unusedParameter = false)
  {
    // Use a short-circuit pattern with a temporary symbol:
    // (Runtime::js_bool($sym = <left>) ? $sym : <right>)
    $symbol = Compiler::generateSymbol("sc");
    return "(Runtime::js_bool(\$$symbol=" . $this->arg1->emit(true) . ")?\$$symbol:" . $this->arg2->emit(true) . ")";
  }
}
