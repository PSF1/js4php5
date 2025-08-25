<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_lt;

/**
 * Stub for BaseConstruct that marks whether emit() was asked for value.
 */
class LtEmitStub extends BaseConstruct
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

final class LtConstructTest extends TestCase
{
  public function testEmitForLtUsesRuntimeCmpWithFlag(): void
  {
    $left  = new LtEmitStub('LEFT');
    $right = new LtEmitStub('RIGHT');

    $node = new c_lt($left, $right);
    $php  = $node->emit();

    // Expect Runtime::cmp(LEFT_gv,RIGHT_gv, 1)
    $this->assertSame('Runtime::cmp(LEFT_gv,RIGHT_gv, 1)', $php);
  }
}
