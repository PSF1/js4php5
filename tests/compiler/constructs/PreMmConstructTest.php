<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_pre_mm;

/**
 * Stub for BaseConstruct that marks whether emit() was asked for value.
 */
class PreMmEmitStub extends BaseConstruct
{
  private string $token;
  public function __construct(string $token) { $this->token = $token; }

  // Match parent's signature (no return type)
  public function emit($getValue = false)
  {
    // Append "_gv" if getValue=true to make it visible (value context)
    return $this->token . ($getValue ? '_gv' : '');
  }
}

final class PreMmConstructTest extends TestCase
{
  public function testEmitForPreDecrementUsesReferenceOfOperand(): void
  {
    $id = new PreMmEmitStub('ID');

    $node = new c_pre_mm($id);
    $php  = $node->emit();

    // Expect Runtime::expr_pre_mm(ID) -> without _gv, because it must be a reference
    $this->assertSame('Runtime::expr_pre_mm(ID)', $php);
  }
}
