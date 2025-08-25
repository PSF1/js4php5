<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_or;

/**
 * Stub for BaseConstruct that marks whether emit() was asked for value.
 */
class OrEmitStub extends BaseConstruct
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

final class OrConstructTest extends TestCase
{
  public function testEmitGeneratesShortCircuitPatternWithTempSymbol(): void
  {
    $left  = new OrEmitStub('LEFT');
    $right = new OrEmitStub('RIGHT');

    $node = new c_or($left, $right);
    $php  = $node->emit();

    // Pattern: (Runtime::js_bool($scN=LEFT_gv)?$scN:RIGHT_gv)
    $this->assertMatchesRegularExpression(
      '/^\(Runtime::js_bool\(\$(sc\d+)=LEFT_gv\)\?\$\1:RIGHT_gv\)$/',
      $php
    );
  }

  public function testEmitAlwaysAsksValueForBothSides(): void
  {
    $left  = new OrEmitStub('LHS');
    $right = new OrEmitStub('RHS');

    $node = new c_or($left, $right);
    $php  = $node->emit();

    $this->assertStringContainsString('LHS_gv', $php);
    $this->assertStringContainsString('RHS_gv', $php);
  }
}
