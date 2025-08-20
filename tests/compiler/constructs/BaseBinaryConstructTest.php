<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseBinaryConstruct;

/**
 * Simple argument stub that exposes an emit() method.
 * It appends "_gv" when $getValue is true to prove the flag is propagated.
 */
class ArgStub
{
  /** @var string */
  private string $out;

  public function __construct(string $out)
  {
    $this->out = $out;
  }

  /** @param bool $getValue */
  public function emit(bool $getValue): string
  {
    // Return an identifier + suffix if getValue is requested
    return $this->out . ($getValue ? '_gv' : '');
  }
}

/**
 * Concrete subclass that follows "c_*" naming, so runtime_op => "fake".
 */
class c_fake extends BaseBinaryConstruct {}

/**
 * Concrete subclass without "c_*" prefix to exercise the fallback.
 */
class NoPrefix extends BaseBinaryConstruct {}

final class BaseBinaryConstructTest extends TestCase
{
  public function testEmitWithBothArgsAndGetValueFlags(): void
  {
    $a1 = new ArgStub('LEFT');
    $a2 = new ArgStub('RIGHT');

    // getValue1=true, getValue2=false
    $node = new c_fake([$a1, $a2], true, false);
    $php = $node->emit();

    $this->assertSame('Runtime::expr_fake(LEFT_gv,RIGHT)', $php);
  }

  public function testEmitWithNullArg2GracefullyOutputsNull(): void
  {
    $a1 = new ArgStub('ONLY');

    // Only one arg provided so arg2 is null
    $node = new c_fake([$a1], false, false);
    $php = $node->emit();

    $this->assertSame('Runtime::expr_fake(ONLY,null)', $php);
  }

  public function testRuntimeOpFallbackWhenClassNameDoesNotMatchPattern(): void
  {
    $a1 = new ArgStub('A');
    $a2 = new ArgStub('B');

    $node = new NoPrefix([$a1, $a2], false, false);
    $php = $node->emit();

    // Fallback uses the short class name as op
    $this->assertSame('Runtime::expr_NoPrefix(A,B)', $php);
  }
}
