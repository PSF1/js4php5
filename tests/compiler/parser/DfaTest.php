<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\parser;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\parser\dfa;

final class DfaTest extends TestCase
{
  public function testNewDfaStartsWithEmptyStateAndInitial(): void
  {
    $d = new dfa();

    // No estados definidos y el estado inicial es cadena vacía por defecto
    $this->assertSame('', $d->initial);
    $this->assertIsArray($d->states);
    $this->assertSame([], $d->states);
  }

  public function testAddStateInitializesFinalDeltaAndMark(): void
  {
    $d = new dfa();
    $label = $d->add_state('S0');

    $this->assertSame('S0', $label);
    $this->assertTrue($d->has_state('S0'));
    $this->assertArrayHasKey('S0', $d->final);
    $this->assertFalse($d->final['S0']);
    $this->assertArrayHasKey('S0', $d->delta);
    $this->assertIsArray($d->delta['S0']);
    $this->assertArrayHasKey('S0', $d->mark);
  }

  public function testAddTransitionAndStepAndAccepting(): void
  {
    $d = new dfa();
    $d->add_state('A');
    $d->add_state('B');

    $d->add_transition('A', 'x', 'B');

    // step debe devolver el destino para el símbolo
    $this->assertSame('B', $d->step('A', 'x'));
    // y null si no existe transición
    $this->assertNull($d->step('A', 'y'));

    // accepting devuelve las claves de símbolos salientes desde el estado
    $this->assertSame(['x'], $d->accepting('A'));
    $this->assertSame([], $d->accepting('B'));
  }

  public function testHasStateFalseForUnknown(): void
  {
    $d = new dfa();
    $this->assertFalse($d->has_state('NOPE'));
  }

  public function testAddExistingStateThrows(): void
  {
    $d = new dfa();
    $d->add_state('S');
    $this->expectException(\RuntimeException::class);
    $d->add_state('S');
  }
}
