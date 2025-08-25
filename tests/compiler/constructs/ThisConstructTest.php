<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\c_this;

final class ThisConstructTest extends TestCase
{
  public function testEmitReturnsRuntimeThisCall(): void
  {
    $node = new c_this();
    $this->assertSame('Runtime::this()', $node->emit());
  }

  public function testEmitIgnoresGetValueParameter(): void
  {
    $node = new c_this();
    $this->assertSame('Runtime::this()', $node->emit(true));
  }
}
