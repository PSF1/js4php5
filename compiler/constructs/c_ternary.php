<?php

namespace js4php5\compiler\constructs;

class c_ternary extends BaseConstruct
{
  /** @var BaseConstruct */
  public $expression;

  /** @var BaseConstruct */
  public $trueStatement;

  /** @var BaseConstruct */
  public $falseStatement;

  /** @var string */
  public $runtime_op;

  /**
   * @param BaseConstruct $expression
   * @param BaseConstruct $trueStatement
   * @param BaseConstruct $falseStatement
   */
  function __construct($expression, $trueStatement, $falseStatement)
  {
    $this->expression     = $expression;
    $this->trueStatement  = $trueStatement;
    $this->falseStatement = $falseStatement;
    $this->runtime_op     = substr($this->className(), 3);
  }

  function emit($unusedParameter = false)
  {
    // Keep parentheses to preserve evaluation order and short-circuit behavior
    return
      '(Runtime::js_bool(' .
      $this->expression->emit(true) .
      ')?(' .
      $this->trueStatement->emit(true) .
      '):(' .
      $this->falseStatement->emit(true) .
      '))';
  }
}
