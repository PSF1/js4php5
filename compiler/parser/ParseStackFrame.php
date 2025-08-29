<?php

namespace js4php5\compiler\parser;

class ParseStackFrame
{
  private string $symbol;
  private array $semantic;
  public $state;

  /**
   * @param string $symbol
   * @param mixed  $state
   */
  public function __construct($symbol, $state)
  {
    $this->symbol = $symbol;
    $this->state = $state;
    $this->semantic = [];
  }

  public function shift($semantic): void
  {
    $this->semantic[] = $semantic;
  }

  public function fold($semantic): void
  {
    $this->semantic = [$semantic];
  }

  /**
   * Return the semantic stack.
   *
   * @return array
   */
  public function semantic(): array
  {
    return $this->semantic;
  }

  /**
   * Return a trace string "symbol : state".
   *
   * @return string
   */
  public function trace(): string
  {
    return "{$this->symbol} : {$this->state}";
  }
}
