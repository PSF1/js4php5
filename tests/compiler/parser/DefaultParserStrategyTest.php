<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\parser;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\parser\DefaultParserStrategy;
use js4php5\compiler\parser\ParseException;
use js4php5\compiler\lexer\Token;
use js4php5\compiler\lexer\Point;
use js4php5\compiler\lexer\Lexer;

final class DefaultParserStrategyTest extends TestCase
{
  public function testAssertDoneDoesNothingWhenTokenTypeIsEmpty(): void
  {
    $strategy = new DefaultParserStrategy();
    $token = new Token('', '', new Point(1, 0), new Point(1, 0));
    $lex = new Lexer(null, ['INITIAL' => []]);
    $lex->start('');
    // No exception expected
    $strategy->assertDone($token, $lex);
    $this->assertTrue(true);
  }

  public function testAssertDoneThrowsWhenTokenTypeIsNotEmpty(): void
  {
    $strategy = new DefaultParserStrategy();
    $token = new Token('T_X', 'x', new Point(1, 1), new Point(1, 2));
    $lex = new Lexer(null, ['INITIAL' => []]);
    $lex->start('');

    $this->expectException(ParseException::class);
    $this->expectExceptionMessage("Parser stuck; source and grammar do not agree.");
    $strategy->assertDone($token, $lex);
  }

  public function testStuckThrowsParseExceptionWithMessage(): void
  {
    $strategy = new DefaultParserStrategy();
    $token = new Token('T_Y', 'y', new Point(2, 3), new Point(2, 4));
    $lex = new Lexer(null, ['INITIAL' => []]);
    $lex->start('');

    try {
      $strategy->stuck($token, $lex, []);
      $this->fail('Expected ParseException was not thrown');
    } catch (ParseException $e) {
      $this->assertStringContainsString("Parser stuck; source and grammar do not agree.", $e->getMessage());
    }
  }
}
