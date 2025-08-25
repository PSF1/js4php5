<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_strict_equal;

/**
 * Stub for BaseConstruct that marks whether emit() was asked for value.
 */
class StrictEqualEmitStub extends BaseConstruct
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

final class StrictEqualConstructTest extends TestCase
{
  public function testEmitForStrictEqualUsesValueOnBothSides(): void
  {
    $left  = new StrictEqualEmitStub('LEFT');
    $right = new StrictEqualEmitStub('RIGHT');

    $node = new c_strict_equal($left, $right);
    $php  = $node->emit();

    // Expect Runtime::expr_strict_equal(LEFT_gv,RIGHT_gv)
    $this->assertSame('Runtime::expr_strict_equal(LEFT_gv,RIGHT_gv)', $php);
  }
}
