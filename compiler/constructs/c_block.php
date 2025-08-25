<?php

namespace js4php5\compiler\constructs;

class c_block extends BaseConstruct
{
  /** @var BaseConstruct[] */
  public $statements;

  /**
   * @param BaseConstruct[] $statements
   */
  function __construct($statements)
  {
    // Ensure we hold an array; (array)null -> []
    $this->statements = is_array($statements) ? $statements : (array)$statements;
  }

  function emit($unusedParameter = false)
  {
    $o = "{\n";
    /** @var BaseConstruct $statement */
    foreach ($this->statements as $statement) {
      // Indent all lines of the emitted statement by two spaces
      $emitted = $statement->emit(true);
      $o .= "  " . trim(str_replace("\n", "\n  ", $emitted)) . "\n";
    }
    $o .= "}\n";
    return $o;
  }
}
