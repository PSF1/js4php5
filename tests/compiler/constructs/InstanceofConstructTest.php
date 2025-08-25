<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_instanceof;

/**
 * Stub for BaseConstruct that marks whether emit() was asked for value.
 */
class InstanceofEmitStub extends BaseConstruct
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

final class InstanceofConstructTest extends TestCase
{
  public function testEmitForInstanceofUsesValueOnBothSides(): void
  {
    $left  = new InstanceofEmitStub('LEFT');
    $right = new InstanceofEmitStub('RIGHT');

    $node = new c_instanceof($left, $right);
    $php  = $node->emit();

    // Expect Runtime::expr_instanceof(LEFT_gv,RIGHT_gv)
    $this->assertSame('Runtime::expr_instanceof(LEFT_gv,RIGHT_gv)', $php);
  }
}
