<?php

namespace js4php5\compiler\parser;

/*
File: set.so.php
License: GPL
Purpose: We should really have a "set" data type. It's too useful.
*/
class set {

  /** @var array<string, mixed> */
  public array $data = [];

  /**
   * Constructor accepts a list of scalar items (string|int).
   *
   * @param array<int|string> $list
   */
  public function __construct(array $list = []) {
    // array_count_values requires scalar values (strings/ints).
    $this->data = array_count_values($list);
  }

  public function has($item): bool {
    return isset($this->data[$item]);
  }

  public function add($item): void {
    $this->data[$item] = TRUE;
  }

  public function del($item): void {
    unset($this->data[$item]);
  }

  public function all(): array {
    return array_keys($this->data);
  }

  public function one(): mixed {
    return key($this->data);
  }

  public function count(): int {
    return count($this->data);
  }

}