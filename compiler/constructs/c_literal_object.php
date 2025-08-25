<?php

namespace js4php5\compiler\constructs;

class c_literal_object extends BaseConstruct
{
  /** @var array<int, BaseConstruct|null> */
  public $obj = [];

  /**
   * @param array<int, BaseConstruct|null>|BaseConstruct|null $o
   */
  function __construct($o = array())
  {
    // Normalize to an array of nodes (or empty array)
    if (is_array($o)) {
      $this->obj = $o;
    } elseif ($o === null) {
      $this->obj = [];
    } else {
      $this->obj = [$o];
    }
  }

  function emit($unusedParameter = false)
  {
    $parts = array();
    $n = count($this->obj);
    for ($i = 0; $i < $n; $i++) {
      $entry = $this->obj[$i] ?? null;
      if ($entry !== null) {
        // Each entry is expected to emit "key,value" so imploding produces K1,V1,K2,V2,...
        $parts[] = $entry->emit(true);
      }
    }
    return "Runtime::literal_object(" . implode(",", $parts) . ")";
  }
}
