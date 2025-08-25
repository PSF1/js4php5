<?php

namespace js4php5\compiler\constructs;

/**
 * Unary delete operator -- Javascript 'delete' operator
 */
class c_delete extends BaseUnaryConstruct
{
  /**
   * @param BaseConstruct|BaseConstruct[] $a       Operand (or array con el operando)
   * @param bool                          $getValue Emitir como valor (true) o referencia (false). Por defecto, referencia.
   */
  function __construct($a, $getValue = false)
  {
    // Accept both a single node and an array of nodes
    $args = is_array($a) ? $a : [$a];
    parent::__construct($args, (bool)$getValue);
  }
}
