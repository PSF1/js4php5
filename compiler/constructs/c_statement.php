<?php

namespace js4php5\compiler\constructs;

class c_statement extends BaseConstruct
{
  /** @var BaseConstruct c_assign|c_call or any construct with emit() */
  public $child;

  /**
   * @param BaseConstruct $child c_assign|c_call or any construct.
   */
  function __construct($child)
  {
    $this->child = $child;
  }

  /**
   * @param bool $unusedParameter
   *
   * @return string PHP Code Chunk
   */
  function emit($unusedParameter = false)
  {
    // Emit child by value and append a single semicolon + newline
    return $this->child->emit(true) . ";\n";
  }
}
