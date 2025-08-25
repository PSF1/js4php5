<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\c_literal_string;

final class LiteralStringConstructTest extends TestCase
{
  public function testSimpleQuotedString(): void
  {
    $node = new c_literal_string("'abc'");
    $this->assertSame("Runtime::js_str('abc')", $node->emit());
  }

  public function testEscapeSequencesCommon(): void
  {
    // \n \t \\ \' \" dentro de comillas simples
    $node = new c_literal_string("'a\\n\\t\\\\\\'\\\"b'");
    $php  = $node->emit();

    // Construye el esperado con newline y tab reales (dobles comillas para interpretar \n y \t)
    // Dentro del literal de PHP que produce var_export:
    // - El backslash queda como \\ (dos backslashes)
    // - La comilla simple se escapa como \'
    // - La comilla doble queda literal
    $expected = "Runtime::js_str('a\n\t\\\\\\'\"b')";
    $this->assertSame($expected, $php);
  }

  public function testHexEscapeX(): void
  {
    $node = new c_literal_string("'\\x41\\x62'"); // 'A' 'b'
    $this->assertSame("Runtime::js_str('Ab')", $node->emit());
  }

  public function testUnicodeEscapeU(): void
  {
    $node = new c_literal_string("'\\u0041\\u0062'"); // 'A' 'b'
    $this->assertSame("Runtime::js_str('Ab')", $node->emit());
  }

  public function testUnknownEscapeFallsBackToChar(): void
  {
    // \q -> 'q' (comportamiento del parser actual)
    $node = new c_literal_string("'\\q'");
    $this->assertSame("Runtime::js_str('q')", $node->emit());
  }

  public function testStripquotesDisabledKeepsOuterQuotesAsContent(): void
  {
    // stripquotes=0 no recorta, por lo que las comillas forman parte del contenido
    $node = new c_literal_string("'xy'", 0);
    $this->assertSame("Runtime::js_str('\\'xy\\'')", $node->emit());
  }
}

