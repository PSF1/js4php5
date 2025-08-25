<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_equal;

/**
 * Stub for BaseConstruct that marks whether emit() was asked for value.
 */
class EqualEmitStub extends BaseConstruct
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

final class EqualConstructTest extends TestCase
{
  public function testEmitForEqualUsesValueOnBothSides(): void
  {
    $left  = new EqualEmitStub('LEFT');
    $right = new EqualEmitStub('RIGHT');

    $node = new c_equal($left, $right);
    $php  = $node->emit();

    // Expect Runtime::expr_equal(LEFT_gv,RIGHT_gv)
    $this->assertSame('Runtime::expr_equal(LEFT_gv,RIGHT_gv)', $php);
  }
}
