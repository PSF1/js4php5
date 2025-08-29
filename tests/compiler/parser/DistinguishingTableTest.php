<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\parser;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\parser\distinguishing_table;

final class DistinguishingTableTest extends TestCase
{
  public function testNewTableStartsEmptyAndDifferIsFalse(): void
  {
    $t = new distinguishing_table();
    $this->assertFalse($t->differ('A', 'B'));
  }

  public function testDistinguishMarksPairRegardlessOfOrder(): void
  {
    $t = new distinguishing_table();

    // Mark A/B as distinguishable
    $t->distinguish('A', 'B');

    // Order should not matter
    $this->assertTrue($t->differ('A', 'B'));
    $this->assertTrue($t->differ('B', 'A'));
  }

  public function testDistinguishSameStateIsIdempotent(): void
  {
    $t = new distinguishing_table();

    // Distinguish the same pair multiple times should not break anything
    $t->distinguish('X', 'Y');
    $t->distinguish('Y', 'X');

    $this->assertTrue($t->differ('X', 'Y'));
  }

  public function testDifferentPairsAreIndependent(): void
  {
    $t = new distinguishing_table();

    $t->distinguish('P', 'Q');
    $this->assertTrue($t->differ('P', 'Q'));
    $this->assertFalse($t->differ('P', 'R'));
    $this->assertFalse($t->differ('Q', 'R'));

    $t->distinguish('Q', 'R');
    $this->assertTrue($t->differ('Q', 'R'));
    $this->assertFalse($t->differ('P', 'R')); // no marcado aún
  }
}
