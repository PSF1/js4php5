<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\parser;

use PHPUnit\Framework\TestCase;

/**
 * Unit tests for js4php5\compiler\parser\set class.
 *
 * These tests validate the behavior of:
 *  - constructor from a list (duplicates handled via array_count_values)
 *  - has(), add(), del()
 *  - all(), one(), count()
 *
 * Note: The library class name is "set" (lowercase). We reference it by FQCN.
 */
final class SetClassTest extends TestCase
{
  public function testConstructFromListAndCountAndAll(): void
  {
    // Provide duplicates; array_count_values will collapse keys but count keys only.
    $s = new \js4php5\compiler\parser\set(['a', 'a', 'b']);

    // all() returns keys (order is the order of first occurrence).
    $all = $s->all();
    $this->assertEquals(['a', 'b'], $all);

    // count() counts distinct keys (not multiplicity)
    $this->assertSame(2, $s->count());

    // has() for present and absent items
    $this->assertTrue($s->has('a'));
    $this->assertTrue($s->has('b'));
    $this->assertFalse($s->has('c'));
  }

  public function testAddAndDelAndOneBehavior(): void
  {
    $s = new \js4php5\compiler\parser\set(['a', 'b']);

    // initial one() returns 'a' (first key)
    $one = $s->one();
    $this->assertSame('a', $one);

    // add a new element 'c'
    $s->add('c');
    $this->assertTrue($s->has('c'));
    $this->assertSame(3, $s->count());

    // delete 'a'
    $s->del('a');
    $this->assertFalse($s->has('a'));
    $this->assertSame(2, $s->count());

    // After deleting 'a', one() should return next key 'b'
    $this->assertSame('b', $s->one());

    // all() should reflect remaining keys (order preserved after deletion)
    $this->assertEquals(['b', 'c'], $s->all());
  }

  public function testAddDuplicateDoesNotIncreaseDistinctCount(): void
  {
    $s = new \js4php5\compiler\parser\set(['x']);
    $this->assertSame(1, $s->count());

    // add duplicate
    // implementation sets value to true, not increment count
    $s->add('x');
    $this->assertSame(1, $s->count());

    // add new distinct
    $s->add('y');
    $this->assertSame(2, $s->count());
  }

  public function testEmptySetOneReturnsNull(): void
  {
    $s = new \js4php5\compiler\parser\set([]);
    // one() should return null (key() on empty array returns null)
    $this->assertNull($s->one());
    $this->assertSame([], $s->all());
    $this->assertSame(0, $s->count());
  }
}
