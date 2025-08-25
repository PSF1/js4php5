<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_bit_not;

/**
 * Stub for BaseConstruct that marks whether emit() was asked for value.
 */
class BitNotEmitStub extends BaseConstruct
{
  private string $token;

  public function __construct(string $token)
  {
    $this->token = $token;
  }

  // Match parent's signature (no return type)
  public function emit($getValue = false)
  {
    // Append "_gv" if getValue=true to make it visible
    return $this->token . ($getValue ? '_gv' : '');
  }
}

final class BitNotConstructTest extends TestCase
{
  public function testEmitForBitNotUsesValueOfOperand(): void
  {
    $expr = new BitNotEmitStub('EXPR');

    $node = new c_bit_not($expr);
    $php  = $node->emit();

    // Expect Runtime::expr_bit_not(EXPR_gv)
    $this->assertSame('Runtime::expr_bit_not(EXPR_gv)', $php);
  }
}
