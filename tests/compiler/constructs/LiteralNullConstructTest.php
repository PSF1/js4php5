<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\c_literal_null;

final class LiteralNullConstructTest extends TestCase
{
  public function testEmitReturnsRuntimeNull(): void
  {
    $node = new c_literal_null();
    $this->assertSame('Runtime::$null', $node->emit());
  }

  public function testEmitIgnoresGetValueParameter(): void
  {
    $node = new c_literal_null();
    // Passing true should not change the output
    $this->assertSame('Runtime::$null', $node->emit(true));
  }
}
