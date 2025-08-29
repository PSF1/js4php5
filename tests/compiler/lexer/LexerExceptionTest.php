<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\lexer;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\lexer\LexerException;

final class LexerExceptionTest extends TestCase
{
  public function testItExtendsBaseExceptionAndCarriesMessage(): void
  {
    $e = new LexerException('oops', 123);
    $this->assertInstanceOf(\Exception::class, $e);
    $this->assertSame('oops', $e->getMessage());
    $this->assertSame(123, $e->getCode());
  }

  public function testCanBeCaughtAsException(): void
  {
    try {
      throw new LexerException('fail');
    } catch (LexerException $e) {
      $this->assertSame('fail', $e->getMessage());
      return; // success path
    }
    $this->fail('Expected LexerException was not thrown');
  }
}
