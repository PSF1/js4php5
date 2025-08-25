<?php

namespace js4php5\compiler\parser;

class state_set_labeler
{
  /** @var array<string,string> Public to avoid dynamic property deprecation in PHP 8.2+ */
  public $map = [];

  /**
   * Modern constructor; initializes internal map.
   */
  public function __construct()
  {
    if (!is_array($this->map)) {
      $this->map = [];
    }
  }

  /**
   * Backward-compat PHP4-style constructor: calls __construct().
   */
  public function state_set_labeler()
  {
    $this->__construct();
  }

  /**
   * Return a stable label for a set of states (order-independent).
   * The first time a set is seen, a new label is generated; subsequent calls
   * with the same set (in any order) return the same label.
   *
   * @param array<int,string> $list
   * @return string
   */
  public function label($list)
  {
    // Normalize: sort and deduplicate to get a canonical key for the set
    $normalized = array_values(array_unique($list));
    sort($normalized);
    $key = implode(':', $normalized);

    if (!isset($this->map[$key])) {
      $this->map[$key] = Helpers::gen_label();
    }
    return $this->map[$key];
  }
}
