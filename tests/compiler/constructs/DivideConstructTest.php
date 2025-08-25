<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_divide;

/**
 * Stub for BaseConstruct that marks whether emit() was asked for value.
 */
class DivideEmitStub extends BaseConstruct
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

final class DivideConstructTest extends TestCase
{
  public function testEmitForDivideUsesValueOnBothSides(): void
  {
    $left  = new DivideEmitStub('LEFT');
    $right = new DivideEmitStub('RIGHT');

    $node = new c_divide($left, $right);
    $php  = $node->emit();

    // Expect Runtime::expr_divide(LEFT_gv,RIGHT_gv)
    $this->assertSame('Runtime::expr_divide(LEFT_gv,RIGHT_gv)', $php);
  }
}
