<?php

namespace js4php5\compiler\constructs;

class c_new extends BaseConstruct
{
  /**
   * @var BaseConstruct
   */
  public $expression;

  /**
   * @var array BaseConstruct[]|scalar[]
   */
  public $args = [];

  function __construct($expression)
  {
    $this->expression = $expression;

    // If direct child is a c_call object, vampirize it.
    if ($this->expression instanceof c_call) {
      // Take callee and arguments from the call; normalize args to array
      $this->args = is_array($this->expression->args) ? $this->expression->args : [$this->expression->args];
      $this->expression = $this->expression->expr;
    } else {
      $this->args = [];
    }
  }

  function emit($unusedParameter = false)
  {
    $args = [];
    foreach ($this->args as $arg) {
      // If it's a construct, ask for its value; otherwise, use literal token
      if (is_object($arg) && method_exists($arg, 'emit')) {
        $args[] = $arg->emit(true);
      } else {
        // Tolerate non-object arguments (e.g., parser provided a scalar token)
        $args[] = (string) $arg;
      }
    }

    // Emit constructor expression by value, and the argument array
    return "Runtime::_new(" . $this->expression->emit(true) . ", array(" . implode(",", $args) . "))";
  }
}
