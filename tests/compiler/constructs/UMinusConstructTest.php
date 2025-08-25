<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_u_minus;

/**
 * Stub for BaseConstruct that marks whether emit() was asked for value.
 */
class UMinusEmitStub extends BaseConstruct
{
  private string $token;
  public function __construct(string $token) { $this->token = $token; }

  // Match parent's signature (no return type)
  public function emit($getValue = false)
  {
    // Append "_gv" if getValue=true to make it visible
    return $this->token . ($getValue ? '_gv' : '');
  }
}

final class UMinusConstructTest extends TestCase
{
  public function testEmitForUnaryMinusUsesValueOfOperand(): void
  {
    $expr = new UMinusEmitStub('EXPR');

    $node = new c_u_minus($expr);
    $php  = $node->emit();

    // Expect Runtime::expr_u_minus(EXPR_gv)
    $this->assertSame('Runtime::expr_u_minus(EXPR_gv)', $php);
  }
}
