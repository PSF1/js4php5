<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\VarDumper;

final class VarDumperEdgeCasesTest extends TestCase
{
  public function testDumpClosedResourceDisplaysMeaningfulToken(): void
  {
    // Create and close a resource; in PHP 7.2+ gettype() can return "resource (closed)"
    $h = fopen('php://temp', 'rb');
    fclose($h);

    // Dump should represent closed resource meaningfully
    $out = VarDumper::dumpAsString($h, '', 10, false);

    // This assertion may FAIL until you add a 'resource (closed)' case in dumpInternal
    $this->assertMatchesRegularExpression('/resource/i', $out);
  }

  /**
   * @requires PHP 7.4
   */
  public function testDumpObjectWithUninitializedTypedPropertyDoesNotThrow(): void
  {
    // Define a class with a typed, uninitialized property
    // Accessing it without checks may throw "Typed property ... must not be accessed before initialization"
    // VarDumper should not crash when dumping such objects.
    $obj = new class {
      public int $n; // uninitialized typed property
    };

    // If dumpInternal casts to (array) and iterates, this may throw in PHP 7.4+.
    // This test may FAIL until you switch to Reflection and check isInitialized().
    $out = VarDumper::dumpAsString($obj, '', 10, false);

    // If no exception is thrown, ensure the output contains the class name
    $this->assertStringContainsString('class@anonymous', $out);
  }

  public function testDumpAsStringWithHighlightShowsLabel(): void
  {
    // With highlighting enabled, label should be injected replacing the "<?php" header
    $out = VarDumper::dumpAsString('abc', 'LBL', 10, true);

    // This may FAIL in PHP 8 if your regex that strips the header no longer matches highlight_string() output
    $this->assertStringContainsString('LBL', $out);
  }
}
