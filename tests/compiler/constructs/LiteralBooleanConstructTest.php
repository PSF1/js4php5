<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\c_literal_boolean;

final class LiteralBooleanConstructTest extends TestCase
{
  public function testEmitReturnsRuntimeTrueForTruthyValues(): void
  {
    // Truthy values should emit Runtime::$true
    $nodeTrue = new c_literal_boolean(true);
    $this->assertSame('Runtime::$true', $nodeTrue->emit());

    $nodeOne = new c_literal_boolean(1);
    $this->assertSame('Runtime::$true', $nodeOne->emit());

    $nodeStr = new c_literal_boolean('non-empty');
    $this->assertSame('Runtime::$true', $nodeStr->emit());
  }

  public function testEmitReturnsRuntimeFalseForFalsyValues(): void
  {
    // Falsy values should emit Runtime::$false
    $nodeFalse = new c_literal_boolean(false);
    $this->assertSame('Runtime::$false', $nodeFalse->emit());

    $nodeZero = new c_literal_boolean(0);
    $this->assertSame('Runtime::$false', $nodeZero->emit());

    $nodeEmptyStr = new c_literal_boolean('');
    $this->assertSame('Runtime::$false', $nodeEmptyStr->emit());
  }

  public function testConstructorNormalizesToBoolean(): void
  {
    $node = new c_literal_boolean(123);
    $this->assertTrue($node->v);

    $node2 = new c_literal_boolean(0);
    $this->assertFalse($node2->v);
  }
}
