<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_pre_pp;

/**
 * Simple stub that shows whether getValue was requested by appending '_gv'.
 */
class PrePpEmitStub extends BaseConstruct
{
  private string $token;
  public function __construct(string $token) { $this->token = $token; }

  // Match parent's signature (no return type)
  public function emit($getValue = false)
  {
    // Append "_gv" when value context is requested
    return $this->token . ($getValue ? '_gv' : '');
  }
}

final class PrePpConstructTest extends TestCase
{
  public function testEmitForPreIncrementUsesReferenceOfOperand(): void
  {
    $id = new PrePpEmitStub('ID');

    $node = new c_pre_pp($id);
    $php  = $node->emit();

    // Pre-increment must operate on a reference, not on the value.
    $this->assertSame('Runtime::expr_pre_pp(ID)', $php);
  }
}
