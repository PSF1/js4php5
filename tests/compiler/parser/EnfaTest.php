<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\parser;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\parser\enfa;
use js4php5\compiler\parser\dfa;

final class EnfaTest extends TestCase
{
  public function testConstructorCreatesInitialAndFinalStates(): void
  {
    $nfa = new enfa();

    // Debe tener estados inicial y final distintos y presentes en la lista de estados
    $this->assertNotSame('', $nfa->initial);
    $this->assertNotSame('', $nfa->final);
    $this->assertNotSame($nfa->initial, $nfa->final);

    $this->assertContains($nfa->initial, $nfa->states);
    $this->assertContains($nfa->final, $nfa->states);

    // Estructuras básicas inicializadas
    $this->assertArrayHasKey($nfa->initial, $nfa->delta);
    $this->assertArrayHasKey($nfa->final, $nfa->delta);
    $this->assertArrayHasKey($nfa->initial, $nfa->epsilon);
    $this->assertArrayHasKey($nfa->final, $nfa->epsilon);
  }

  public function testEpsilonClosureIncludesReachableStates(): void
  {
    $nfa = new enfa();

    // Añadir transición epsilon initial -> final
    $nfa->add_epsilon($nfa->initial, $nfa->final);

    $closure = $nfa->eclose([$nfa->initial]);

    $this->assertContains($nfa->initial, $closure);
    $this->assertContains($nfa->final, $closure);
  }

  public function testRecognizeAddsTransitionAndStepReachesFinal(): void
  {
    $nfa = new enfa();
    $nfa->recognize('a');

    // start_states devuelve e-closure(initial)
    $start = $nfa->start_states();
    $this->assertContains($nfa->initial, $start);

    // step desde el conjunto start con 'a' debe incluir final en su cierre
    $dest = $nfa->step($start, 'a');
    $this->assertContains($nfa->final, $dest);

    // accepting sobre start incluye 'a' como símbolo aceptable
    $this->assertContains('a', $nfa->accepting($start));
  }

  public function testDeterminizeSimpleRecognizer(): void
  {
    $nfa = new enfa();
    $nfa->recognize('a');

    $d = $nfa->determinize();
    $this->assertInstanceOf(dfa::class, $d);

    // Debe existir una transición 'a' desde el estado inicial a algún estado destino
    $init = $d->initial;
    $this->assertNotSame('', $init);
    $outSymbols = $d->accepting($init);
    $this->assertContains('a', $outSymbols);

    $dest = $d->step($init, 'a');
    $this->assertNotNull($dest);
    // El estado destino debe marcarse final (aceptación)
    $this->assertTrue(isset($d->final[$dest]) ? $d->final[$dest] : false);
  }

  public function testAddExistingStateThrows(): void
  {
    $nfa = new enfa();
    $label = 'Q';
    $nfa->add_state($label);

    $this->expectException(\RuntimeException::class);
    $nfa->add_state($label);
  }
}
