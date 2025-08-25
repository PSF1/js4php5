<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_if;

/**
 * Condición: marca getValue con sufijo para comprobar propagación.
 */
class IfCondStub extends BaseConstruct
{
  public function emit($getValue = false)
  {
    return $getValue ? 'COND_gv' : 'COND';
  }
}

/**
 * Bloque: devuelve un bloque con llaves y salto de línea final.
 */
class IfBlockStub extends BaseConstruct
{
  private string $code;
  public function __construct(string $code) { $this->code = $code; }
  public function emit($getValue = false)
  {
    // Simula el formato de c_block: llaves y newline al final
    return "{\n  " . str_replace("\n", "\n  ", trim($this->code)) . "\n}\n";
  }
}

final class IfConstructTest extends TestCase
{
  public function testIfWithoutElseEmitsIfBlockOnly(): void
  {
    $cond = new IfCondStub();
    $if   = new IfBlockStub("doIf();");
    $node = new c_if($cond, $if);

    $out = $node->emit();

    $this->assertSame("if (Runtime::js_bool(COND_gv)) " . $if->emit(true), $out);
  }

  public function testIfWithElseEmitsElseExactlyOnceAndKeepsNewline(): void
  {
    $cond = new IfCondStub();
    $if   = new IfBlockStub("left();");
    $else = new IfBlockStub("right();");

    $node = new c_if($cond, $if, $else);
    $out  = $node->emit();

    $expected = rtrim("if (Runtime::js_bool(COND_gv)) " . $if->emit(true))
      . " else "
      . rtrim($else->emit(true))
      . "\n";

    $this->assertSame($expected, $out);
  }

  public function testIfSkipsElseWhenNull(): void
  {
    $cond = new IfCondStub();
    $if   = new IfBlockStub("onlyIf();");
    $node = new c_if($cond, $if, null);

    $out = $node->emit();

    $this->assertSame("if (Runtime::js_bool(COND_gv)) " . $if->emit(true), $out);
  }
}
