<?php

namespace js4php5\runtime;

class jsContext
{
  /** @var jsObject The bound "this" for the current execution context */
  public $js_this;

  /** @var jsObject[] Scope chain (lexical environments) for identifier resolution */
  public $scope_chain;

  /** @var jsObject The variable/activation object for this context */
  public $var;

  /**
   * Execution context container.
   *
   * @param jsObject   $that         The current "this" object.
   * @param jsObject[] $scope_chain  Scope chain for identifier lookup.
   * @param jsObject   $var          Variable/activation object.
   */
  function __construct($that, $scope_chain, $var)
  {
    $this->js_this = $that;
    $this->scope_chain = $scope_chain;
    $this->var = $var;
  }
}
