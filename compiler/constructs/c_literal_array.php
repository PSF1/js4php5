<?php

namespace js4php5\compiler\constructs;

class c_literal_array extends BaseConstruct
{
  /** @var array<int, BaseConstruct|null> */
  public $arr = [];

  /**
   * @param array<int, BaseConstruct|null>|BaseConstruct|null $arr
   */
  function __construct($arr)
  {
    // Normalize input to an array of nodes (or empty array)
    if (is_array($arr)) {
      $this->arr = $arr;
    } elseif ($arr === null) {
      $this->arr = [];
    } else {
      // Single node passed
      $this->arr = [$arr];
    }
  }

  function emit($unusedParameter = false)
  {
    $items = [];
    $n = count($this->arr);

    // If the literal is a single "null" node, emit an empty array literal
    if ($n === 1 && ($this->arr[0] instanceof c_literal_null)) {
      $items = [];
    } else {
      // Collect non-null entries
      for ($i = 0; $i < $n; $i++) {
        $node = $this->arr[$i] ?? null;
        if ($node !== null) {
          $items[] = $node->emit(true);
        }
      }
    }

    return 'Runtime::literal_array(' . implode(',', $items) . ')';
  }
}
