<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_not;

/**
 * Stub for BaseConstruct that marks whether emit() was asked for value.
 */
class NotEmitStub extends BaseConstruct
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

final class NotConstructTest extends TestCase
{
  public function testEmitForLogicalNotUsesValueOfOperand(): void
  {
    $expr = new NotEmitStub('EXPR');

    $node = new c_not($expr);
    $php  = $node->emit();

    // Expect Runtime::expr_not(EXPR_gv)
    $this->assertSame('Runtime::expr_not(EXPR_gv)', $php);
  }
}
