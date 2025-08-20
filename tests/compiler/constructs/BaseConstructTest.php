<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;

final class BaseConstructTest extends TestCase
{
  public function testClassNameReturnsShortName(): void
  {
    // Define a small named subclass for testing
    require_once __DIR__ . '/FakeConstruct.php';

    $this->assertSame('FakeConstruct', \js4php5\tests\compiler\constructs\FakeConstruct::className());
  }

  public function testEmitContractUsingAConcreteStub(): void
  {
    // Anonymous subclass implementing emit() to verify the signature/contract
    $node = new class extends BaseConstruct {
      // Implement emit() to return a different string depending on $getValue.
      public function emit($getValue = false): string
      {
        // Simple output used to verify the $getValue flag propagation
        return $getValue ? 'VALUE' : 'REF';
      }
    };

    $this->assertSame('REF', $node->emit(false));
    $this->assertSame('VALUE', $node->emit(true));
  }
}
