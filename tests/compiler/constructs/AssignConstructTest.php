<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_assign;

/**
 * Stub for BaseConstruct that marks whether emit() was asked for value.
 */
class AssignEmitStub extends BaseConstruct
{
  private string $token;

  public function __construct(string $token)
  {
    $this->token = $token;
  }

  // Match parent's signature (no return type)
  public function emit($getValue = false)
  {
    // Append "_gv" if getValue=true to make it visible
    return $this->token . ($getValue ? '_gv' : '');
  }
}

final class AssignConstructTest extends TestCase
{
  public function testEmitForAssignmentUsesRefOnLeftAndValueOnRight(): void
  {
    $left  = new AssignEmitStub('LEFT');   // will be emitted without getValue
    $right = new AssignEmitStub('RIGHT');  // will be emitted with getValue=true

    $node = new c_assign($left, $right);
    $php  = $node->emit();

    // Expect Runtime::expr_assign(LEFT,RIGHT_gv)
    $this->assertSame('Runtime::expr_assign(LEFT,RIGHT_gv)', $php);
  }
}
