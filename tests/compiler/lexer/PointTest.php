<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\lexer;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\lexer\Point;

final class PointTest extends TestCase
{
  public function testConstructorCastsToIntAndGettersReturnValues(): void
  {
    // Pass numeric strings to ensure the constructor casts to int
    $p = new Point('10', '20');

    // getLine() and getCol() should return ints
    $this->assertSame(10, $p->getLine());
    $this->assertSame(20, $p->getCol());
    $this->assertIsInt($p->getLine());
    $this->assertIsInt($p->getCol());
  }

  public function testWorksWithIntsDirectly(): void
  {
    $p = new Point(3, 7);
    $this->assertSame(3, $p->getLine());
    $this->assertSame(7, $p->getCol());
  }
}
