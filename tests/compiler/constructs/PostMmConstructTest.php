<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_post_mm;

/**
 * Stub for BaseConstruct that marks whether emit() was asked for value.
 */
class PostMmEmitStub extends BaseConstruct
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

final class PostMmConstructTest extends TestCase
{
  public function testEmitForPostDecrementUsesReferenceOfOperand(): void
  {
    $id = new PostMmEmitStub('ID');

    $node = new c_post_mm($id);
    $php  = $node->emit();

    // Expect Runtime::expr_post_mm(ID) -> without _gv, because it must be a reference
    $this->assertSame('Runtime::expr_post_mm(ID)', $php);
  }
}
