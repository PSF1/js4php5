<?php

namespace js4php5\compiler\constructs;

class c_case extends BaseConstruct
{
  /** @var BaseConstruct|null */
  public $expr;

  /** @var BaseConstruct[] */
  public $code;

  /** @var string Symbol name of the switch expression (e.g., "sw") */
  public $e;

  /**
   * @param BaseConstruct|null $expr  Case expression; null means "default"
   * @param BaseConstruct[]    $code  Statements inside the case
   * @param string             $switchSymbol Symbol for the switch expression variable
   */
  function __construct($expr, $code, $switchSymbol = 'sw')
  {
    $this->expr = $expr;
    $this->code = is_array($code) ? $code : (array)$code;
    $this->e    = $switchSymbol;
  }

  function emit($unusedParameter = false)
  {
    if ($this->expr === null) {
      $o = "  default:\n";
    } else {
      $o = "  case (Runtime::js_bool(Runtime::expr_strict_equal(\$" . $this->e . "," . $this->expr->emit(true) . "))):\n";
    }
    foreach ($this->code as $code) {
      $o .= "    " . trim(str_replace("\n", "\n    ", $code->emit(true))) . "\n";
    }
    return $o;
  }
}
