<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\lexer;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\lexer\Stream;
use js4php5\compiler\lexer\Point;

final class StreamTest extends TestCase
{
  public function testTestMatchesFromStartAndConsumes(): void
  {
    $s = new Stream('123abc');

    // Pattern with delimiters; 'A' anchor is añadido por Stream::test
    $m = $s->test('/\d+/');

    $this->assertIsArray($m);
    $this->assertSame('123', $m[0]);

    // Después de consumir '123', la posición debe reflejar col=3, line=1
    $pos = $s->pos();
    $this->assertInstanceOf(Point::class, $pos);
    $this->assertSame(1, $pos->getLine());
    $this->assertSame(3, $pos->getCol());
  }

  public function testDefaultRuleConsumesSingleCharWhenNoPatternMatches(): void
  {
    $s = new Stream('Ab');
    // Sin llamar a test(), defaultRule debe consumir un carácter
    $s->defaultRule();
    $pos1 = $s->pos();
    $this->assertSame(1, $pos1->getLine());
    $this->assertSame(1, $pos1->getCol());

    // Otra llamada avanza otro carácter
    $s->defaultRule();
    $pos2 = $s->pos();
    $this->assertSame(2, $pos2->getCol());
  }
}
