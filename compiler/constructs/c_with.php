<?php

namespace js4php5\compiler\constructs;

class c_with extends BaseConstruct
{
    /** @var BaseConstruct */
    public $expr;

    /** @var BaseConstruct */
    public $statement;
  
    function __construct($expr, $statement)
    {
        list($this->expr, $this->statement) = func_get_args();
    }

    function emit($unusedParameter = false)
    {
        $o = "Runtime::push_scope(Runtime::js_obj(" . $this->expr->emit(true) . "));\n";
        $o .= $this->statement->emit(true);
        $o .= "Runtime::pop_scope();\n";
        return $o;
    }
}

