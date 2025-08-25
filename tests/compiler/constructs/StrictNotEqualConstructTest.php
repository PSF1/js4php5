<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_strict_not_equal;

/**
 * Stub for BaseConstruct that marks whether emit() was asked for value.
 */
class StrictNotEqualEmitStub extends BaseConstruct
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

final class StrictNotEqualConstructTest extends TestCase
{
  public function testEmitForStrictNotEqualUsesValueOnBothSides(): void
  {
    $left  = new StrictNotEqualEmitStub('LEFT');
    $right = new StrictNotEqualEmitStub('RIGHT');

    $node = new c_strict_not_equal($left, $right);
    $php  = $node->emit();

    // Expect Runtime::expr_strict_not_equal(LEFT_gv,RIGHT_gv)
    $this->assertSame('Runtime::expr_strict_not_equal(LEFT_gv,RIGHT_gv)', $php);
  }
}
