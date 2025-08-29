<?php
declare(strict_types=1);

namespace js4php5\tests\compiler;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\Compiler;

/**
 * Tests for js4php5\compiler\Compiler.
 *
 * These tests try to be non-invasive: they use the real Lexer/Parser
 * from the package rather than redefining them. To avoid static-state
 * interference between tests (Compiler uses static variables inside
 * compile()), each test runs in a separate PHP process.
 */
final class CompilerClassTest extends TestCase
{
  /**
   * Ensure generateSymbol produces monotonically increasing unique tokens.
   *
   * We run in a separate process to ensure the internal static counter
   * inside generateSymbol is fresh for the test process.
   *
   * @runInSeparateProcess
   */
  public function testGenerateSymbolIncrements(): void
  {
    $a = Compiler::generateSymbol('pfx_');
    $b = Compiler::generateSymbol('pfx_');

    $this->assertIsString($a);
    $this->assertIsString($b);
    $this->assertStringStartsWith('pfx_', $a);
    $this->assertStringStartsWith('pfx_', $b);
    $this->assertNotSame($a, $b, 'generateSymbol should return distinct values on subsequent calls');
  }

  /**
   * A simple compile smoke test: compile a tiny JS snippet and assert
   * we get back a non-empty PHP source string that appears to contain
   * runtime helpers (the emitted code in this project typically refers
   * to the Runtime class).
   *
   * @runInSeparateProcess
   */
  public function testCompileSimpleScriptReturnsPhpString(): void
  {
    $js = 'var x = 1; x;';
    $php = Compiler::compile($js);

    $this->assertIsString($php, 'compile() must return a string (PHP source)');
    $this->assertNotEmpty($php, 'compiled PHP should not be empty');

    // The emitter in this project commonly emits Runtime:: calls; assert at least that substring appears.
    $this->assertStringContainsString('Runtime::', $php, 'compiled PHP should reference Runtime:: (expected in emitted code)');
  }

  /**
   * Compiling two different scripts should produce reasonably different outputs.
   * We don't assert exact equality or exact contents; we assert the outputs are strings
   * and that, for two clearly different inputs, the outputs are not identical.
   *
   * @runInSeparateProcess
   */
  public function testCompileDifferentScriptsProduceDifferentOutput(): void
  {
    $js1 = 'var a = 10; a;';
    $js2 = 'var b = "hello"; b;';

    $php1 = Compiler::compile($js1);
    $php2 = Compiler::compile($js2);

    $this->assertIsString($php1);
    $this->assertIsString($php2);
    $this->assertNotEmpty($php1);
    $this->assertNotEmpty($php2);

    // It's possible (though unlikely) that two different scripts produce identical emitted source;
    // we assert that this is not the common case for these different scripts.
    $this->assertNotSame($php1, $php2, 'Different JS inputs should normally yield different compiled PHP outputs');
  }

  /**
   * If the emitter throws an exception during compilation, Compiler::compile()
   * is expected to catch it and emit a "Compilation Error" message. This test
   * attempts to detect that path by passing an input likely to trigger a compilation-time
   * problem (malformed JS). We assert that the compiler does not throw an exception
   * to the caller and that the output contains "Compilation Error" if anything was printed.
   *
   * Note: This test is conservative: depending on the parser/lexer behavior, the parser
   * may detect syntax errors earlier and throw different exceptions. The test verifies
   * that compile() does not propagate an uncaught exception.
   *
   * @runInSeparateProcess
   */
  public function testCompileHandlesErrorsGracefully(): void
  {
    $jsBad = '+';

    // Capture any output produced by compile (Compiler echoes "Compilation Error: ..." on exceptions).
    ob_start();
    try {
      $php = \js4php5\compiler\Compiler::compile($jsBad);
      $output = ob_get_clean();
    } catch (\Throwable $e) {
      ob_end_clean(); // limpiar buffer si existe
      // If parser threw, assert it's the expected parse exception type (or a subclass).
      $this->assertInstanceOf(\js4php5\compiler\parser\ParseException::class, $e);
      return;
    }

    // If we reach here, no exception was thrown. Validate return type and optional output.
    $this->assertTrue(is_string($php) || $php === null, 'compile() should return string or null even on bad input (not throw)');
    if ($output !== '') {
      $this->assertStringContainsString('Compilation Error', $output);
    }
  }
}
