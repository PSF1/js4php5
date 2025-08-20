<?php

namespace js4php5\compiler\constructs;

/**
 * Construct for the Javascript 'dot' operator.
 */
class c_accessor extends BaseConstruct
{
  /** @var BaseConstruct */
  public $obj;

  /** @var BaseConstruct */
  public $member;

  /** @var bool */
  public $resolve;

  /**
   * @param BaseConstruct $obj
   * @param BaseConstruct $member
   * @param bool          $resolve
   */
  function __construct($obj, $member, $resolve)
  {
    $this->obj = $obj;
    $this->member = $member;
    // Normalize to boolean to avoid null/0/1 surprises
    $this->resolve = (bool) $resolve;
  }

  /**
   * @inheritdoc
   */
  function emit($getValue = false)
  {
    // Select Runtime::dot vs Runtime::dotv based on $getValue
    $v = $getValue ? 'v' : '';
    // Always emit object with getValue=true; emit member according to $this->resolve
    return 'Runtime::dot' . $v . '(' . $this->obj->emit(true) . ',' . $this->member->emit($this->resolve) . ')';
  }
}
