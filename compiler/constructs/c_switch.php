<?php

namespace js4php5\compiler\constructs;

use js4php5\compiler\Compiler;

class c_switch extends BaseConstruct
{
  /** @var BaseConstruct */
  public $expr;

  /** @var BaseConstruct[] c_case[] */
  public $block = [];

  /**
   * @param BaseConstruct     $expr
   * @param BaseConstruct[]|BaseConstruct $block c_case[] o un solo c_case
   */
  function __construct($expr, $block)
  {
    $this->expr  = $expr;
    $this->block = is_array($block) ? $block : [$block];
  }

  function emit($unusedParameter = false)
  {
    $e = Compiler::generateSymbol("jsrt_sw");
    // Aumentar anidamiento y garantizar restauración
    c_source::$nest++;
    try {
      $o  = "\$$e = " . $this->expr->emit(true) . ";\n";
      $o .= "switch (true) {\n";
      foreach ($this->block as $case) {
        // Comunicar a cada case el símbolo del switch
        if (is_object($case)) {
          $case->e = $e;
        }
        $o .= $case->emit(true);
      }
      $o .= "\n}\n";
      return $o;
    } finally {
      c_source::$nest--;
    }
  }
}
