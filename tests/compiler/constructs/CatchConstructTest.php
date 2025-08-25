<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_catch;
use js4php5\compiler\constructs\c_block;

class CatchBlockStub extends BaseConstruct
{
  private string $code;
  public function __construct(string $code) { $this->code = $code; }
  public function emit($getValue = false)
  {
    return $this->code;
  }
}

final class CatchConstructTest extends TestCase
{
  public function testEmitDelegatesToCodeBlock(): void
  {
    $block = new CatchBlockStub("line1;\nline2;");
    $node  = new c_catch('e', $block);

    $this->assertSame("line1;\nline2;", $node->emit());
  }

  public function testConstructorStoresId(): void
  {
    $block = new CatchBlockStub("noop;");
    $node  = new c_catch('err', $block);

    $this->assertSame('err', $node->id);
  }

  public function testEmitWorksWithRealCBlock(): void
  {
    $stmt1 = new CatchBlockStub("s1();");
    $stmt2 = new CatchBlockStub("s2();");
    $block = new c_block([$stmt1, $stmt2]);

    $node  = new c_catch('e', $block);
    $expected = "{\n  s1();\n  s2();\n}\n";

    $this->assertSame($expected, $node->emit());
  }
}
