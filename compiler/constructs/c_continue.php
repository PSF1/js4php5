<?php

namespace js4php5\compiler\constructs;

class c_continue extends BaseConstruct
{
  /** @var string Continue target label; ';' significa continue sin etiqueta */
  public $label;

  /**
   * @param string $label
   */
  function __construct($label)
  {
    // Normaliza a string por si llega null/numérico
    $this->label = (string) $label;
  }

  function emit($unusedParameter = false)
  {
    // Fuera de bucle/switch: comport. legado -> banner de error
    if (c_source::$nest <= 0) {
      return "ERROR: continue outside of a loop\n*************************\n\n";
    }

    // Continue sin etiqueta
    if ($this->label === ';') {
      return "continue;\n";
    }

    // Continue etiquetado: verifica que la etiqueta exista para evitar 'undefined index'
    if (isset(c_source::$labels[$this->label])) {
      $depth = (int) (c_source::$nest - c_source::$labels[$this->label]);
      // Evita "continue 0;" si hay desajuste en los contadores
      if ($depth < 1) {
        $depth = 1;
      }
      return "continue $depth;\n";
    }

    // Etiqueta desconocida: fallback seguro a continue sin etiqueta
    return "continue;\n";
  }
}
