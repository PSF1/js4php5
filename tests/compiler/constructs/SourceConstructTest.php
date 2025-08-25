<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_program;
use js4php5\compiler\constructs\c_source;

/**
 * Simple statement stub that returns a fixed code string.
 */
class SrcStmtStub extends BaseConstruct
{
  private string $code;
  public function __construct(string $code) { $this->code = $code; }
  public function emit($getValue = false)
  {
    return $this->code;
  }
}

/**
 * Function mapping/declaration stubs exposing function_emit()/toplevel_emit().
 */
class SrcFuncMapStub
{
  private string $code;
  public function __construct(string $code) { $this->code = $code; }
  public function function_emit(): string { return $this->code; }
}

class SrcFuncDeclStub
{
  private string $code;
  public function __construct(string $code) { $this->code = $code; }
  public function toplevel_emit(): string { return $this->code; }
}

final class SourceConstructTest extends TestCase
{
  protected function setUp(): void
  {
    // Reset static state that c_source manipula
    c_source::$that = null;
    c_source::$nest = 0;
    c_source::$labels = [];
    // Reset la referencia estática en c_program por si algún test anterior la dejó
    // No hay API pública para resetear, se reescribe con el constructor de c_program.
  }

  public function testEmitTopLevelWrapsRunAndInitializesJs(): void
  {
    // Source con un par de statements
    $s1 = new SrcStmtStub("line1;\n");
    $s2 = new SrcStmtStub("line2;\n");
    $src = new c_source([$s1, $s2], []); // sin funciones ni vars

    // Marcar este source como toplevel (c_program::$source === $src)
    new c_program($src);

    $out = $src->emit();

    // Debe envolver en un método run con JS::init() y contener los statements
    $this->assertStringContainsString('static public function run()', $out);
    $this->assertStringContainsString('JS::init();', $out);
    $this->assertStringContainsString('line1;', $out);
    $this->assertStringContainsString('line2;', $out);
  }

  public function testEmitNonTopLevelConcatenatesFunctionMappingVarsAndStatements(): void
  {
    $s = new SrcStmtStub("S1;\n");
    $src = new c_source([$s], []);

    // Añadir un mapeo de función
    $src->functions = [new SrcFuncMapStub("MAP1;\n")];

    $out = $src->emit();

    // Para non-toplevel: no debe envolver en run()
    $this->assertStringNotContainsString('static public function run()', $out);
    // Debe incluir el bloque de "function mapping"
    $this->assertStringContainsString("/* function mapping */\n", $out);
    $this->assertStringContainsString("MAP1;", $out);
    $this->assertStringContainsString("S1;", $out);
  }

  public function testAddFunctionExpressionAppendsToCurrentSource(): void
  {
    $src = new c_source([], []);
    // Simular que este es el current "that"
    c_source::$that = $src;

    $f = new SrcFuncMapStub("FM;\n");
    c_source::addFunctionExpression($f);

    $this->assertContains($f, $src->functions);
  }

  public function testAddFunctionDefinitionAppendsToProgramTopLevel(): void
  {
    $src = new c_source([], []);
    new c_program($src); // marca $src como toplevel

    $f = new SrcFuncDeclStub("FD;\n");
    c_source::addFunctionDefinition($f);

    $this->assertContains($f, $src->funcdef);
  }

  public function testEmitRestoresStaticThatAfterEmission(): void
  {
    $placeholder = new c_source([], []);
    c_source::$that = $placeholder;

    $src = new c_source([new SrcStmtStub("ONE;\n")], []);
    $src->emit();

    // Debe restaurar el valor anterior
    $this->assertSame($placeholder, c_source::$that);
  }
}
