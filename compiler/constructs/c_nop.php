<?php

namespace js4php5\compiler\constructs;

class c_nop extends BaseConstruct
{
  function emit($unusedParameter = false)
  {
    // Emit an empty code block.
    return '{}';
  }
}
