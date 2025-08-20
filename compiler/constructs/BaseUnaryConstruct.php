<?php

namespace js4php5\compiler\constructs;

abstract class BaseUnaryConstruct extends BaseConstruct
{
  /** @var BaseConstruct|null */
  public $arg;

  /** @var bool */
  public $getValue;

  /** @var string */
  public $runtime_op;

  /**
   * @param BaseConstruct[] $args
   * @param bool            $getValue
   */
  function __construct($args, $getValue = false)
  {
    // Be safe when the parser provides fewer args
    $this->arg = $args[0] ?? null;
    $this->getValue = (bool)$getValue;

    // Requires compiler construct files be prefixed c_
    // Make extraction robust and provide fallback
    $class = $this->className();
    if (preg_match('/^c_([A-Za-z0-9_]+)$/', $class, $match)) {
      $this->runtime_op = $match[1];
    } else {
      // Fallback: use the class short name as the op identifier
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
    // Emit Runtime call; handle missing arg defensively
    $argCode = $this->arg ? $this->arg->emit($this->getValue) : 'null';
    return 'Runtime::expr_' . $this->runtime_op . '(' . $argCode . ')';
  }
}
