<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_call;

class CallExprStub extends BaseConstruct
{
  private string $expr;
  public function __construct(string $expr) { $this->expr = $expr; }
  public function emit($getValue = false)
  {
    // For the callee, emit without forcing getValue in test
    return $this->expr;
  }
}

class CallArgStub extends BaseConstruct
{
  private string $token;
  public function __construct(string $token) { $this->token = $token; }
  public function emit($getValue = false)
  {
    return $this->token . ($getValue ? '_gv' : '');
  }
}

final class CallConstructTest extends TestCase
{
  public function testEmitBuildsRuntimeCallWithArgsByValue(): void
  {
    $callee = new CallExprStub('CALLEE');
    $a1 = new CallArgStub('A1');
    $a2 = new CallArgStub('A2');

    $node = new c_call($callee, [$a1, $a2]);
    $php  = $node->emit();

    $this->assertSame('Runtime::call(CALLEE, array(A1_gv,A2_gv))', $php);
  }

  public function testEmitWithNoArgsBuildsEmptyArray(): void
  {
    $callee = new CallExprStub('FN');
    $node   = new c_call($callee, []);
    $php    = $node->emit();

    $this->assertSame('Runtime::call(FN, array())', $php);
  }
}
