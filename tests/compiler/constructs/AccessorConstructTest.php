<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_accessor;

/**
 * Stub for BaseConstruct that returns a token and marks whether emit() requested value.
 */
class AccessorEmitStub extends BaseConstruct
{
  private string $token;

  public function __construct(string $token)
  {
    $this->token = $token;
  }

  // Match parent's signature: no return type
  public function emit($getValue = false)
  {
    // Append suffix to make visible whether getValue was true
    return $this->token . ($getValue ? '_gv' : '');
  }
}

final class AccessorConstructTest extends TestCase
{
  public function testEmitUsesDotOrDotvDependingOnGetValue(): void
  {
    $obj    = new AccessorEmitStub('OBJ');
    $member = new AccessorEmitStub('MEM');

    // getValue=false -> dot(...)
    $node = new c_accessor($obj, $member, false);
    $php = $node->emit(false);
    $this->assertSame('Runtime::dot(OBJ_gv,MEM)', $php, 'obj should be emitted with getValue=true, member with resolve=false');

    // getValue=true -> dotv(...)
    $php2 = $node->emit(true);
    $this->assertSame('Runtime::dotv(OBJ_gv,MEM)', $php2);
  }

  public function testEmitPropagatesResolveFlagToMember(): void
  {
    $obj    = new AccessorEmitStub('LHS');
    $member = new AccessorEmitStub('RHS');

    // resolve=true -> member should be emitted with getValue=true
    $node = new c_accessor($obj, $member, true);
    $php = $node->emit(false);

    $this->assertSame('Runtime::dot(LHS_gv,RHS_gv)', $php);
  }
}
