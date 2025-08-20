<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\BaseUnaryConstruct;

/**
 * Simple argument stub that exposes an emit() method.
 * It appends "_gv" when $getValue is true to prove flag propagation.
 */
class UnaryArgStub extends BaseConstruct
{
  private string $out;
  public function __construct(string $out) { $this->out = $out; }

  // Match parent's signature: no return type
  public function emit($getValue = false)
  {
    return $this->out . ($getValue ? '_gv' : '');
  }
}

/**
 * Concrete subclass that follows "c_*" naming, so runtime_op => "unop".
 */
class c_unop extends BaseUnaryConstruct {}

/**
 * Concrete subclass without "c_*" prefix to exercise the fallback.
 */
class NoPrefixUnary extends BaseUnaryConstruct {}

final class BaseUnaryConstructTest extends TestCase
{
  public function testEmitPropagatesGetValueAndUsesDerivedOp(): void
  {
    $arg = new UnaryArgStub('ARG');
    $node = new c_unop([$arg], true);
    $php = $node->emit();

    $this->assertSame('Runtime::expr_unop(ARG_gv)', $php);
  }

  public function testEmitWithMissingArgEmitsNull(): void
  {
    $node = new c_unop([], false);
    $php = $node->emit();

    $this->assertSame('Runtime::expr_unop(null)', $php);
  }

  public function testRuntimeOpFallbackWhenClassNameDoesNotMatchPattern(): void
  {
    $arg = new UnaryArgStub('X');
    $node = new NoPrefixUnary([$arg], false);
    $php = $node->emit();

    $this->assertSame('Runtime::expr_NoPrefixUnary(X)', $php);
  }
}
