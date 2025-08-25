<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\parser;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\parser\ParseException;
use js4php5\compiler\lexer\Token;
use js4php5\compiler\lexer\Point;

final class ParseExceptionTest extends TestCase
{
  public function testConstructorSetsMessageTokenPosAndCode(): void
  {
    $start = new Point(3, 5);
    $stop  = new Point(3, 6);
    $tok   = new Token('T_ID', 'x', $start, $stop);

    $e = new ParseException('parser error', $tok, $start, 42);

    $this->assertSame('parser error', $e->getMessage());
    $this->assertSame(42, $e->getCode());
    $this->assertSame($tok, $e->getToken());
    $this->assertSame($start, $e->getPos());
    $this->assertInstanceOf(Point::class, $e->getPos());
  }

  public function testPreviousExceptionIsChained(): void
  {
    $start = new Point(1, 0);
    $tok   = new Token('T_EOF', '', $start, $start);

    $prev = new \RuntimeException('root cause', 7);
    $e = new ParseException('wrap', $tok, $start, 0, $prev);

    $this->assertSame($prev, $e->getPrevious());
    $this->assertSame('root cause', $e->getPrevious()->getMessage());
  }
}
