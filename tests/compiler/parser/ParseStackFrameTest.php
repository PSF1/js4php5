<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\parser;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\parser\ParseStackFrame;

/**
 * Tests for the ParseStackFrame class.
 *
 * These tests verify that:
 *  - constructor sets the initial state and symbol
 *  - shift() appends semantic values
 *  - fold() replaces the semantic array with a single element
 *  - semantic() returns the current semantic stack
 *  - trace() returns "symbol : state"
 *  - the public state property is mutable and reflected in trace()
 */
final class ParseStackFrameTest extends TestCase
{
  /**
   * Ensure constructor initializes state and semantic stack correctly,
   * and that trace() reports "symbol : state".
   */
  public function testConstructorInitializesFields(): void
  {
    $frame = new ParseStackFrame('SYM', 'STATE1');

    // state should be set and semantic should start empty
    $this->assertSame('STATE1', $frame->state);
    $this->assertSame([], $frame->semantic());

    // trace should reflect symbol and state
    $this->assertSame('SYM : STATE1', $frame->trace());
  }

  /**
   * shift() should append items to the internal semantic array in order.
   */
  public function testShiftAppendsSemantic(): void
  {
    $frame = new ParseStackFrame('S', 'ST');

    $frame->shift('a');
    $frame->shift(123);

    $obj = new \stdClass();
    $obj->x = 'y';
    $frame->shift($obj);

    // The semantic stack should preserve insertion order and values.
    $this->assertSame(['a', 123, $obj], $frame->semantic());
  }

  /**
   * fold() should replace the semantic stack with a single-element array containing the given value.
   */
  public function testFoldReplacesSemantic(): void
  {
    $frame = new ParseStackFrame('X', 'S0');

    // populate some values first
    $frame->shift('first');
    $frame->shift('second');

    // fold should discard previous values and set one element array
    $frame->fold('foldval');
    $this->assertSame(['foldval'], $frame->semantic());
  }

  /**
   * The public state property must be mutable and trace() should reflect changes.
   */
  public function testStateIsMutableAndTraceReflectsChange(): void
  {
    $frame = new ParseStackFrame('T', 'OLD');
    $this->assertSame('T : OLD', $frame->trace());

    // Mutate the public state and assert trace updates accordingly.
    $frame->state = 'NEW';
    $this->assertSame('T : NEW', $frame->trace());
  }
}
