<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\VarDumper;

final class VarDumperHappyPathTest extends TestCase
{
  public function testDumpAsStringPlainText(): void
  {
    // Dump a simple string without highlighting to avoid HTML differences
    $out = VarDumper::dumpAsString('foo', '', 10, false);

    // Should contain quoted string
    $this->assertIsString($out); // Basic type check
    $this->assertStringContainsString("'foo'", $out);
  }

  public function testDumpArrayDepthLimit(): void
  {
    // Build a nested array to check depth limiting
    $data = ['a' => ['b' => ['c' => 1]]];

    // Depth = 2 should elide deeper levels
    $out = VarDumper::dumpAsString($data, '', 2, false);

    // Expect an ellipsis for deeper levels
    $this->assertStringContainsString('[...]', $out);
  }

  public function testDumpObjectWithRecursion(): void
  {
    // Create a self-referential object to test cycle handling
    $o = new stdClass();
    $o->self = $o;

    $out = VarDumper::dumpAsString($o, '', 10, false);

    // Should contain class name and recursion abbreviation "(...)"
    $this->assertStringContainsString('stdClass#', $out);
    $this->assertStringContainsString('(...)', $out);
  }

  public function testExportArrayProducesParsablePhp(): void
  {
    // export() should produce a PHP expression that evals back to the same value
    $arr = ['x' => 1, 'y' => 2];
    $code = VarDumper::export($arr);

    // Evaluate the generated code
    /** @var array $restored */
    $restored = eval('return ' . $code . ';');

    $this->assertSame($arr, $restored);
    $this->assertStringStartsWith('[', trim($code)); // short array syntax
  }

  public function testExportObjectRoundTrip(): void
  {
    // Objects are exported via serialize/unserialize
    $obj = new stdClass();
    $obj->x = 1;

    $code = VarDumper::export($obj);

    // Evaluate the generated code to restore the object
    /** @var object $restored */
    $restored = eval('return ' . $code . ';');

    $this->assertInstanceOf(stdClass::class, $restored);
    // Use property_exists for PHPUnit 9/10/11 compatibility
    $this->assertTrue(property_exists($restored, 'x'));
    $this->assertSame(1, $restored->x);
    $this->assertStringStartsWith('unserialize(', trim($code));
  }
}
