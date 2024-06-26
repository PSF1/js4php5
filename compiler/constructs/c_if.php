<?php

namespace js4php5\compiler\constructs;

class c_if extends BaseConstruct
{

    /**
     * Constructor.
     *
     * @param \js4php5\compiler\constructs\c_literal_boolean|mixed $cond Condition.
     * @param \js4php5\compiler\constructs\c_block|mixed $ifblock If block.
     * @param \js4php5\compiler\constructs\c_block|\js4php5\compiler\constructs\c_nop|mixed $elseblock Else block.
     */
    function __construct($cond, $ifblock, $elseblock = null)
    {
        $this->cond = $cond;
        $this->ifblock = $ifblock;
        $this->elseblock = $elseblock;
    }

    function emit($unusedParameter = false)
    {
        $o = "if (Runtime::js_bool(" . $this->cond->emit(true) . ")) " . $this->ifblock->emit(true);
        // If we have a "else" block and it's not empty.
        if ($this->elseblock && !$this->elseblock instanceof c_nop) {
            // Parse "else" block.
            $o = rtrim($o) . " else " . $this->elseblock->emit(true) . "\n";
        }
        return $o;
    }
}

