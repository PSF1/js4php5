<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_compound_assign;

class CompoundAssignLhsStub extends BaseConstruct
{
  private string $token;
  public function __construct(string $token) { $this->token = $token; }
  // LHS must be emitted as reference (no getValue), so just ignore the flag and return token
  public function emit($getValue = false) { return $this->token; }
}

class CompoundAssignRhsStub extends BaseConstruct
{
  private string $token;
  public function __construct(string $token) { $this->token = $token; }
  // RHS must be emitted with getValue=true; we append _gv to assert propagation
  public function emit($getValue = false) { return $this->token . ($getValue ? '_gv' : ''); }
}

final class CompoundAssignConstructTest extends TestCase
{
  /**
   * @dataProvider mappings
   */
  public function testMappingsGenerateExpectedRuntimeOp(string $op, string $expected): void
  {
    $lhs = new CompoundAssignLhsStub('LHS');
    $rhs = new CompoundAssignRhsStub('RHS');

    $node = new c_compound_assign($lhs, $rhs, $op);
    $php  = $node->emit();

    $this->assertSame("Runtime::expr_assign(LHS,RHS_gv,'$expected')", $php);
  }

  public function mappings(): array
  {
    return [
      ['*=',  'expr_multiply'],
      ['/=',  'expr_divide'],
      ['%=',  'expr_modulo'],
      ['+=',  'expr_plus'],
      ['-=',  'expr_minus'],
      ['<<=', 'expr_lsh'],
      ['>>=', 'expr_rsh'],
      ['>>>=','expr_ursh'],
      ['&=',  'expr_bit_and'],
      ['^=',  'expr_bit_xor'],
      ['|=',  'expr_bit_or'],
    ];
  }

  public function testUnknownOperatorThrows(): void
  {
    $lhs = new CompoundAssignLhsStub('A');
    $rhs = new CompoundAssignRhsStub('B');

    $this->expectException(\InvalidArgumentException::class);
    new c_compound_assign($lhs, $rhs, '**=');
  }
}
