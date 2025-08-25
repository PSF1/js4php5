<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\c_identifier;

final class IdentifierConstructTest extends TestCase
{
  public function testEmitReturnsRuntimeIdReferenceByDefault(): void
  {
    $node = new c_identifier('myVar');
    $php  = $node->emit(false);

    // Default: reference context -> Runtime::id('myVar')
    $this->assertSame("Runtime::id('myVar')", $php);
  }

  public function testEmitWithGetValueTrueUsesIdv(): void
  {
    $node = new c_identifier('myVar');
    $php  = $node->emit(true);

    // Value context -> Runtime::idv('myVar')
    $this->assertSame("Runtime::idv('myVar')", $php);
  }

  public function testStoresIdentifierAsString(): void
  {
    // Even if a non-string is passed, it should be normalized to string
    $node = new c_identifier(123);
    $this->assertSame('123', $node->id);
  }
}
