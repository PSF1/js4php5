<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_plus;

/**
 * Stub for BaseConstruct that marks whether emit() was asked for value.
 */
class PlusEmitStub extends BaseConstruct
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

final class PlusConstructTest extends TestCase
{
  public function testEmitForPlusUsesValueOnBothSides(): void
  {
    $left  = new PlusEmitStub('LEFT');
    $right = new PlusEmitStub('RIGHT');

    $node = new c_plus($left, $right);
    $php  = $node->emit();

    // Expect Runtime::expr_plus(LEFT_gv,RIGHT_gv)
    $this->assertSame('Runtime::expr_plus(LEFT_gv,RIGHT_gv)', $php);
  }
}
