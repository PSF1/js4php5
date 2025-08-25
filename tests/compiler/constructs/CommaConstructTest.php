<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_comma;

/**
 * Stub for BaseConstruct that marks whether emit() was asked for value.
 */
class CommaEmitStub extends BaseConstruct
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

final class CommaConstructTest extends TestCase
{
  public function testEmitForCommaUsesValueOnBothSides(): void
  {
    $left  = new CommaEmitStub('LEFT');
    $right = new CommaEmitStub('RIGHT');

    $node = new c_comma($left, $right);
    $php  = $node->emit();

    // Expect Runtime::expr_comma(LEFT_gv,RIGHT_gv)
    $this->assertSame('Runtime::expr_comma(LEFT_gv,RIGHT_gv)', $php);
  }
}
