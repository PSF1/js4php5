<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\c_nop;

final class NopConstructTest extends TestCase
{
  public function testEmitReturnsEmptyBlock(): void
  {
    $node = new c_nop();
    $this->assertSame("{}", $node->emit());
  }

  public function testEmitIgnoresGetValueParameter(): void
  {
    $node = new c_nop();
    $this->assertSame("{}", $node->emit(true));
  }
}
