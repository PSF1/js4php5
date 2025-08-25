<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_typeof;

/**
 * Stub for BaseConstruct that marks whether emit() was asked for value.
 */
class TypeofEmitStub extends BaseConstruct
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

final class TypeofConstructTest extends TestCase
{
  public function testEmitForTypeofUsesValueOfOperand(): void
  {
    $id = new TypeofEmitStub('EXPR');

    $node = new c_typeof($id);
    $php  = $node->emit();

    // Expect Runtime::expr_typeof(EXPR_gv)
    $this->assertSame('Runtime::expr_typeof(EXPR_gv)', $php);
  }
}
