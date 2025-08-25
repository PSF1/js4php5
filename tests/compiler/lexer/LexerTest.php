<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\lexer;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\lexer\Lexer;
use js4php5\compiler\lexer\LexerException;

final class LexerTest extends TestCase
{
  public function testNextWithoutStartThrowsLexerNotStarted(): void
  {
    $lexer = new Lexer(/* init_context */ null);
    $this->expectException(LexerException::class);
    $this->expectExceptionMessage('Lexer has not been started');
    $lexer->next();
  }

  public function testUnknownInitialStateThrows(): void
  {
    // Patrones sólo para un estado distinto; el estado por defecto es 'INITIAL'
    $patterns = ['OTHER' => []];
    $lexer = new Lexer(null, $patterns);
    $lexer->start(''); // construir megaRegexp
    $this->expectException(LexerException::class);
    $this->expectExceptionMessage("No lexer state called 'INITIAL'");
    $lexer->next();
  }
}
