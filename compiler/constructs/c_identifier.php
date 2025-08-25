<?php

namespace js4php5\compiler\constructs;

/**
 * JavaScript Identifier
 */
class c_identifier extends BaseConstruct
{
  /** @var string */
  public $id;

  /**
   * @param string $id
   */
  function __construct($id)
  {
    // Normalize to string defensively
    $this->id = (string) $id;
  }

  /**
   * @param bool $getValue
   *
   * @return string
   */
  function emit($getValue = false)
  {
    // Use Runtime::id (reference) or Runtime::idv (value) depending on context
    $v = $getValue ? "v" : "";
    return "Runtime::id$v('{$this->id}')";
  }
}
