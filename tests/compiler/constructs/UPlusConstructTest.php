<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_u_plus;

/**
 * Stub for BaseConstruct that marks whether emit() was asked for value.
 */
class UPlusEmitStub extends BaseConstruct
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

final class UPlusConstructTest extends TestCase
{
  public function testEmitForUnaryPlusUsesValueOfOperand(): void
  {
    $expr = new UPlusEmitStub('EXPR');

    $node = new c_u_plus($expr);
    $php  = $node->emit();

    // Expect Runtime::expr_u_plus(EXPR_gv)
    $this->assertSame('Runtime::expr_u_plus(EXPR_gv)', $php);
  }
}
