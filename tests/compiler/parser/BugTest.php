<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\parser;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\parser\Bug;

final class BugTest extends TestCase
{
  public function testItExtendsBaseExceptionAndCarriesMessage(): void
  {
    $e = new Bug('parser bug', 99);
    $this->assertInstanceOf(\Exception::class, $e);
    $this->assertSame('parser bug', $e->getMessage());
    $this->assertSame(99, $e->getCode());
  }

  public function testCanBeCaughtAsBug(): void
  {
    try {
      throw new Bug('boom');
    } catch (Bug $e) {
      $this->assertSame('boom', $e->getMessage());
      return; // success
    }
    $this->fail('Expected Bug exception was not thrown');
  }
}
