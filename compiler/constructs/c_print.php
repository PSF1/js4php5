<?php

namespace js4php5\compiler\constructs;

class c_print extends BaseConstruct
{
  /** @var array<int, mixed> */
  public $args;

  function __construct()
  {
    // Capture variadic args safely
    $this->args = func_get_args();
  }

  function emit($unusedParameter = false)
  {
    $parts = [];
    foreach ($this->args as $arg) {
      // If it's a construct, emit its value; otherwise treat as a literal token
      if (is_object($arg) && method_exists($arg, 'emit')) {
        $parts[] = '(' . $arg->emit(true) . ')';
      } else {
        $parts[] = '(' . (string)$arg . ')';
      }
    }

    // If no args, avoid introducing a double space between parentheses
    if (empty($parts)) {
      return 'Runtime::write( );' . "\n";
    }

    // Normal path with arguments, keep spacing before closing parenthesis
    return 'Runtime::write( ' . implode(',', $parts) . " );\n";
  }
}
