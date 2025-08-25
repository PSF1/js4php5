<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_ternary;

/**
 * Stub that appends "_gv" when value context is requested.
 */
class TernaryEmitStub extends BaseConstruct
{
  private string $token;
  public function __construct(string $token) { $this->token = $token; }
  public function emit($getValue = false)
  {
    return $this->token . ($getValue ? '_gv' : '');
  }
}

final class TernaryConstructTest extends TestCase
{
  public function testEmitBuildsJsBoolTernaryAndRequestsValueFromAllParts(): void
  {
    $cond  = new TernaryEmitStub('COND');
    $whenT = new TernaryEmitStub('TRUE');
    $whenF = new TernaryEmitStub('FALSE');

    $node = new c_ternary($cond, $whenT, $whenF);
    $php  = $node->emit();

    // Expect: (Runtime::js_bool(COND_gv)?(TRUE_gv):(FALSE_gv))
    $this->assertSame('(Runtime::js_bool(COND_gv)?(TRUE_gv):(FALSE_gv))', $php);
  }

  public function testEmitKeepsParenthesesAroundBranches(): void
  {
    $cond  = new TernaryEmitStub('C');
    $whenT = new TernaryEmitStub('A');
    $whenF = new TernaryEmitStub('B');

    $node = new c_ternary($cond, $whenT, $whenF);
    $out  = $node->emit();

    $this->assertStringContainsString('?(A_gv):', $out);
    $this->assertStringContainsString(':(B_gv)', $out);
    $this->assertMatchesRegularExpression('/^\(Runtime::js_bool\(.+\)\?\(.+\):\(.+\)\)$/', $out);
  }
}
