<?php

namespace js4php5\compiler\constructs;

class c_compound_assign extends BaseConstruct
{
  /** @var BaseConstruct */
  public $a;

  /** @var BaseConstruct */
  public $b;

  /** @var string */
  public $op;

  /**
   * @param BaseConstruct $a
   * @param BaseConstruct $b
   * @param string        $op
   */
  function __construct($a, $b, $op)
  {
    $this->a = $a;
    $this->b = $b;
    $this->op = (string)$op;

    // Validate operator early to avoid undefined mapping at emit time
    static $valid = ['*=', '/=', '%=', '+=', '-=', '<<=', '>>=', '>>>=', '&=', '^=', '|='];
    if (!in_array($this->op, $valid, true)) {
      // Throw a clear error instead of producing invalid code
      throw new \InvalidArgumentException("Unsupported compound assignment operator: {$this->op}");
    }
  }

  /**
   * @param bool $unusedParameter
   *
   * @return string PHP code chunk
   */
  function emit($unusedParameter = false)
  {
    // Map JS compound operator to Runtime expr_* name
    $map = [
      '*='   => 'expr_multiply',
      '/='   => 'expr_divide',
      '%='   => 'expr_modulo',
      '+='   => 'expr_plus',
      '-='   => 'expr_minus',
      '<<='  => 'expr_lsh',
      '>>='  => 'expr_rsh',
      '>>>= '=> 'expr_ursh', // space-safe key below, see normalization
      '&='   => 'expr_bit_and',
      '^='   => 'expr_bit_xor',
      '|='   => 'expr_bit_or',
    ];

    // Normalize key for >>>=
    $key = ($this->op === '>>>=') ? '>>>= ' : $this->op;
    $s = $map[$key];

    // LHS: reference (emit without getValue), RHS: value (emit with getValue=true)
    return "Runtime::expr_assign(" . $this->a->emit() . "," . $this->b->emit(true) . ",'" . $s . "')";
  }
}
