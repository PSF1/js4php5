<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_multiply;

/**
 * Stub for BaseConstruct that marks whether emit() was asked for value.
 */
class MultiplyEmitStub extends BaseConstruct
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

final class MultiplyConstructTest extends TestCase
{
  public function testEmitForMultiplyUsesValueOnBothSides(): void
  {
    $left  = new MultiplyEmitStub('LEFT');
    $right = new MultiplyEmitStub('RIGHT');

    $node = new c_multiply($left, $right);
    $php  = $node->emit();

    // Expect Runtime::expr_multiply(LEFT_gv,RIGHT_gv)
    $this->assertSame('Runtime::expr_multiply(LEFT_gv,RIGHT_gv)', $php);
  }
}
