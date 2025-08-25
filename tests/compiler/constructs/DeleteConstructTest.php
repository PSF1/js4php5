<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_delete;

/**
 * Stub for BaseConstruct that marks whether emit() was asked for value.
 */
class DeleteEmitStub extends BaseConstruct
{
  private string $token;
  public function __construct(string $token) { $this->token = $token; }

  // Match parent's signature (no return type)
  public function emit($getValue = false)
  {
    // Append "_gv" when getValue=true to make it visible
    return $this->token . ($getValue ? '_gv' : '');
  }
}

final class DeleteConstructTest extends TestCase
{
  public function testEmitRequestsReferenceByDefault(): void
  {
    $expr = new DeleteEmitStub('EXPR');

    // Default getValue=false => reference context
    $node = new c_delete([$expr]); // pass as array
    $php  = $node->emit();

    // Expect Runtime::expr_delete(EXPR)
    $this->assertSame('Runtime::expr_delete(EXPR)', $php);
  }

  public function testEmitCanRequestValueIfExplicitlyAsked(): void
  {
    $expr = new DeleteEmitStub('TARGET');

    $node = new c_delete([$expr], true);
    $php  = $node->emit();

    // Expect value context on operand
    $this->assertSame('Runtime::expr_delete(TARGET_gv)', $php);
  }

  public function testConstructorAcceptsSingleNodeWithoutArray(): void
  {
    $expr = new DeleteEmitStub('ONE');

    // Pass a single node (not array); constructor must wrap it safely
    $node = new c_delete($expr, false);
    $php  = $node->emit();

    $this->assertSame('Runtime::expr_delete(ONE)', $php);
  }
}
