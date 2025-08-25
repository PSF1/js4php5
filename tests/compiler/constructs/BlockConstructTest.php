<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_block;

/**
 * Stub for statements that returns a multi-line string to test indentation.
 */
class BlockStmtStub extends BaseConstruct
{
  private string $code;
  public function __construct(string $code) { $this->code = $code; }
  public function emit($getValue = false)
  {
    // Emit multi-line code to check indentation behavior
    return $this->code;
  }
}

final class BlockConstructTest extends TestCase
{
  public function testEmitWithMultipleStatementsAddsBracesAndIndentation(): void
  {
    $s1 = new BlockStmtStub("line1;\nline1b;");
    $s2 = new BlockStmtStub("line2;");

    $block = new c_block([$s1, $s2]);
    $php   = $block->emit();

    $expected = "{\n"
      . "  line1;\n"
      . "  line1b;\n"
      . "  line2;\n"
      . "}\n";

    $this->assertSame($expected, $php);
  }

  public function testEmitWithEmptyStatementsProducesEmptyBlock(): void
  {
    $block = new c_block([]);
    $php   = $block->emit();

    $this->assertSame("{\n}\n", $php);
  }
}
