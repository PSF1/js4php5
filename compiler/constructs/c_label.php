<?php

namespace js4php5\compiler\constructs;

class c_label extends BaseConstruct
{
  /**
   * @var string
   */
  public $label;

  /**
   * @var BaseConstruct c_statement|c_block
   */
  public $block;

  /**
   * @param string        $label
   * @param BaseConstruct $block c_statement|c_block
   */
  function __construct($label, $block)
  {
    // Normalize label to string and remove any suffix after colon
    $this->label = (string) $label;
    $parts = explode(':', $this->label, 2);
    $this->label = $parts[0];

    $this->block = $block;
  }

  function emit($unusedParameter = false)
  {
    // Associate this label with current nesting level
    c_source::$labels[$this->label] = c_source::$nest;

    // Delegate to the block (expression context)
    return $this->block->emit(true);
  }
}
