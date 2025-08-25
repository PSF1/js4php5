<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_gte;

/**
 * Stub for BaseConstruct that marks whether emit() was asked for value.
 */
class GteEmitStub extends BaseConstruct
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

final class GteConstructTest extends TestCase
{
  public function testEmitForGteUsesValueOnBothSides(): void
  {
    $left  = new GteEmitStub('LEFT');
    $right = new GteEmitStub('RIGHT');

    $node = new c_gte($left, $right);
    $php  = $node->emit();

    // Expect Runtime::expr_gte(LEFT_gv,RIGHT_gv)
    $this->assertSame('Runtime::expr_gte(LEFT_gv,RIGHT_gv)', $php);
  }
}
