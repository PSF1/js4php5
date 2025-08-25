<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_lte;

/**
 * Stub for BaseConstruct that marks whether emit() was asked for value.
 */
class LteEmitStub extends BaseConstruct
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

final class LteConstructTest extends TestCase
{
  public function testEmitForLteUsesValueOnBothSides(): void
  {
    $left  = new LteEmitStub('LEFT');
    $right = new LteEmitStub('RIGHT');

    $node = new c_lte($left, $right);
    $php  = $node->emit();

    // Expect Runtime::expr_lte(LEFT_gv,RIGHT_gv)
    $this->assertSame('Runtime::expr_lte(LEFT_gv,RIGHT_gv)', $php);
  }
}
