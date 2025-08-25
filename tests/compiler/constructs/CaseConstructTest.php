<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_case;

class CaseExprStub extends BaseConstruct
{
  private string $code;
  public function __construct(string $code) { $this->code = $code; }
  public function emit($getValue = false)
  {
    return $this->code;
  }
}

class CaseStmtStub extends BaseConstruct
{
  private string $code;
  public function __construct(string $code) { $this->code = $code; }
  public function emit($getValue = false)
  {
    return $this->code;
  }
}

final class CaseConstructTest extends TestCase
{
  public function testDefaultCaseEmitsDefaultLabel(): void
  {
    $node = new c_case(null, [new CaseStmtStub('doDefault();')], 'sw');
    $code = $node->emit();

    $expected = "  default:\n"
      . "    doDefault();\n";
    $this->assertSame($expected, $code);
  }

  public function testCaseEmitsStrictEqualAgainstSwitchSymbol(): void
  {
    $expr = new CaseExprStub('EXPR');
    $stmts = [new CaseStmtStub('s1();'), new CaseStmtStub('s2();')];

    $node = new c_case($expr, $stmts, 'sw');
    $code = $node->emit();

    $expected = "  case (Runtime::js_bool(Runtime::expr_strict_equal(\$sw,EXPR))):\n"
      . "    s1();\n"
      . "    s2();\n";

    $this->assertSame($expected, $code);
  }
}
