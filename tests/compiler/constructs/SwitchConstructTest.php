<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_switch;
use js4php5\compiler\constructs\c_case;
use js4php5\compiler\constructs\c_source;

/**
 * Expresión del switch: emite un token y marca getValue con _gv.
 */
class SwitchExprStub extends BaseConstruct
{
  private string $token;
  public function __construct(string $token) { $this->token = $token; }
  public function emit($getValue = false)
  {
    return $this->token . ($getValue ? '_gv' : '');
  }
}

/**
 * Statement stub para los cuerpos de los case/default.
 */
class SwitchStmtStub extends BaseConstruct
{
  private string $code;
  public function __construct(string $code) { $this->code = $code; }
  public function emit($getValue = false)
  {
    return $this->code;
  }
}

final class SwitchConstructTest extends TestCase
{
  protected function setUp(): void
  {
    c_source::$nest = 0;
    c_source::$labels = [];
  }

  public function testEmitBuildsSwitchTrueWithCasesAndDefault(): void
  {
    $expr = new SwitchExprStub('EXPR');

    // case: EX1 -> two statements
    $case1 = new c_case(
      new class('EX1') extends BaseConstruct {
        private string $t;
        public function __construct(string $t) { $this->t = $t; }
        public function emit($getValue = false) { return $this->t; }
      },
      [new SwitchStmtStub("do1();"), new SwitchStmtStub("do2();")]
    );

    // default (expr = null)
    $default = new c_case(null, [new SwitchStmtStub("doDefault();")]);

    $node = new c_switch($expr, [$case1, $default]);
    $out  = $node->emit();

    // Primera línea: asignación del símbolo temporal
    $this->assertMatchesRegularExpression('/^\$jsrt_sw\d+\s=\sEXPR_gv;$/m', $out);

    // Debe abrir con "switch (true) {"
    $this->assertStringContainsString("switch (true) {\n", $out);

    // El case debe usar Runtime::expr_strict_equal($sym,EX1)
    $this->assertMatchesRegularExpression('/case\s+\(Runtime::js_bool\(Runtime::expr_strict_equal\(\$jsrt_sw\d+,EX1\)\)\):/', $out);

    // Debe contener los statements con indentación
    $this->assertStringContainsString("  case (", $out);
    $this->assertStringContainsString("    do1();\n", $out);
    $this->assertStringContainsString("    do2();\n", $out);

    // Debe contener default
    $this->assertStringContainsString("  default:\n", $out);
    $this->assertStringContainsString("    doDefault();\n", $out);

    // Debe cerrar con "}\n" (precedido por una línea en blanco según implementación)
    $this->assertStringContainsString("\n}\n", $out);
  }

  public function testConstructorAcceptsSingleCaseNode(): void
  {
    $expr = new SwitchExprStub('E');
    $only = new c_case(null, [new SwitchStmtStub("one();")]);

    // Pasar un solo nodo en lugar de array
    $node = new c_switch($expr, $only);
    $out  = $node->emit();

    $this->assertStringContainsString("default:", $out);
    $this->assertStringContainsString("one();", $out);
  }

  public function testNestCounterIsRestoredAfterEmit(): void
  {
    $start = 7;
    c_source::$nest = $start;

    $expr = new SwitchExprStub('X');
    $node = new c_switch($expr, []);

    $node->emit();
    $this->assertSame($start, c_source::$nest);
  }
}
