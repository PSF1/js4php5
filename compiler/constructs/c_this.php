<?php

namespace js4php5\compiler\constructs;

class c_this extends BaseConstruct
{
  function emit($unusedParameter = false)
  {
    // Emit call to fetch current JS "this" object at runtime
    return "Runtime::this()";
  }
}
