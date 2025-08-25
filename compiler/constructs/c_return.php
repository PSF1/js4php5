<?php

namespace js4php5\compiler\constructs;

class c_return extends BaseConstruct
{
  /** @var BaseConstruct|string|null */
  public $expr;

  /**
   * @param BaseConstruct|string|null $expr Nodo de expresión o ';' para indicar "return;" sin expresión.
   */
  function __construct($expr)
  {
    $this->expr = $expr;
  }

  function emit($unusedParameter = false)
  {
    // Permitir devolver valores al llamador de PHP: si no hay expresión, devolvemos undefined.
    if ($this->expr === ';' || $this->expr === null) {
      return "return Runtime::\$undefined;\n";
    }

    // Return con expresión: emitir el valor de la expresión
    return "return " . $this->expr->emit(true) . ";\n";
  }
}
