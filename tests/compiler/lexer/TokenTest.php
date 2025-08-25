<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\lexer;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\lexer\Token;
use js4php5\compiler\lexer\Point;

final class TokenTest extends TestCase
{
  public function testConstructorAndGettersReturnExpectedValues(): void
  {
    $start = new Point(1, 2);
    $stop  = new Point(1, 5);

    $t = new Token('T_ID', 'abc', $start, $stop);

    $this->assertSame('T_ID', $t->getType());
    $this->assertSame('abc', $t->getText());
    $this->assertSame($start, $t->getStart());
    $this->assertSame($stop, $t->getStop());
    $this->assertInstanceOf(Point::class, $t->getStart());
    $this->assertInstanceOf(Point::class, $t->getStop());
  }

  public function testGetNullTokenHasEmptyTypeTextAndMinusOnePoints(): void
  {
    $t = Token::getNullToken();

    $this->assertSame('', $t->getType());
    $this->assertSame('', $t->getText());
    $this->assertSame(-1, $t->getStart()->getLine());
    $this->assertSame(-1, $t->getStart()->getCol());
    $this->assertSame(-1, $t->getStop()->getLine());
    $this->assertSame(-1, $t->getStop()->getCol());
  }
}
