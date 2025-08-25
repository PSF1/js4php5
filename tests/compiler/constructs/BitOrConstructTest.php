<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_bit_or;

/**
 * Stub for BaseConstruct that marks whether emit() was asked for value.
 */
class BitOrEmitStub extends BaseConstruct
{
  private string $token;
  public function __construct(string $token) { $this->token = $token; }
  public function emit($getValue = false)
  {
    // Return token plus suffix if getValue was requested
    return $this->token . ($getValue ? '_gv' : '');
  }
}

final class BitOrConstructTest extends TestCase
{
  public function testEmitForBitOrUsesValueOnBothSides(): void
  {
    $left  = new BitOrEmitStub('LEFT');
    $right = new BitOrEmitStub('RIGHT');

    $node = new c_bit_or($left, $right);
    $php  = $node->emit();

    // Expect Runtime::expr_bit_or(LEFT_gv,RIGHT_gv)
    $this->assertSame('Runtime::expr_bit_or(LEFT_gv,RIGHT_gv)', $php);
  }
}
