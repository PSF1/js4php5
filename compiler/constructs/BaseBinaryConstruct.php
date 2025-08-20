<?php

namespace js4php5\compiler\constructs;

use js4php5\VarDumper;

/**
 * js4php5
 * Abstract base class for binary and bitwise operators (&&, ||, &, |, ^, etc.)
 */
abstract class BaseBinaryConstruct extends BaseConstruct
{
  /** @var BaseConstruct|null */
  public $arg1;

  /** @var BaseConstruct|null */
  public $arg2;

  /** @var bool */
  public $getValue1;

  /** @var bool */
  public $getValue2;

  /** @var string */
  public $runtime_op;

  /**
   * @param BaseConstruct[] $args
   * @param bool            $getValue1
   * @param bool            $getValue2
   */
  function __construct($args, $getValue1 = false, $getValue2 = false)
  {
    // Be safe with missing indexes when the parser provides fewer args
    $this->arg1 = $args[0] ?? null;
    $this->arg2 = $args[1] ?? null;
    $this->getValue1 = (bool)$getValue1;
    $this->getValue2 = (bool)$getValue2;

    // Requires compiler construct files be prefixed c_
    // Make the extraction robust in case the class does not follow the pattern
    $class = $this->className();
    if (preg_match('/^c_([A-Za-z0-9_]+)$/', $class, $match)) {
      $this->runtime_op = $match[1];
    } else {
      // Fallback: use the class name itself as the operation identifier
      // (keeps previous behavior from tests and avoids undefined index notices)
      $this->runtime_op = $class;
    }
  }

  /**
   * @param bool $unusedParameter Ignored.
   *
   * @return string PHP code chunk
   */
  function emit($unusedParameter = false)
  {
    // Build Runtime expression call; if arg2 is missing, emit "null" safely
    $php =
      'Runtime::expr_' .
      $this->runtime_op .
      '(' .
      ($this->arg1 ? $this->arg1->emit($this->getValue1) : 'null') .
      ',';

    if ($this->arg2 === null) {
      $php .= 'null';
    } else {
      $php .= $this->arg2->emit($this->getValue2);
    }

    $php .= ')';

    return $php;
  }
}
