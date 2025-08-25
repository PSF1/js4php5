<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\c_literal_number;

final class LiteralNumberConstructTest extends TestCase
{
  public function testEmitWithInteger(): void
  {
    $node = new c_literal_number(123);
    $this->assertSame('Runtime::js_int(123)', $node->emit());
  }

  public function testEmitWithFloat(): void
  {
    $node = new c_literal_number(3.14);
    $this->assertSame('Runtime::js_int(3.14)', $node->emit());
  }

  public function testEmitWithNegative(): void
  {
    $node = new c_literal_number(-42);
    $this->assertSame('Runtime::js_int(-42)', $node->emit());
  }

  public function testEmitWithScientificNotation(): void
  {
    $node = new c_literal_number('1e3');
    $this->assertSame('Runtime::js_int(1e3)', $node->emit());
  }

  public function testEmitWithHexLiteralKeepsLiteral(): void
  {
    // Algunos literales en JS pueden expresarse en hex; el compilador los emite tal cual.
    $node = new c_literal_number('0xFF');
    $this->assertSame('Runtime::js_int(0xFF)', $node->emit());
  }
}
