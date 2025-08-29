<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Integration test: verify that the current compiler/runtime executes
 * the provided JavaScript fragment with the same semantics as the original.
 *
 * We run each case in a separate process to avoid caching of generated PHP classes.
 */
final class GeneratedScriptEquivalenceTest extends TestCase
{
  /**
   * The original JS body (from the auto-generated PHP file).
   * We will prepend `var amount = X;` to test different inputs.
   */
  private string $originalJs = <<<'JS'
var plazos = 1;

if(amount > 998) {
    plazos = 12;
} else if(amount > 600) {
    plazos = 10;
} else if(amount > 300) {
    plazos = 6;
} else if(amount > 150) {
    plazos = 4;
} else if(amount > 100) {
    plazos = 3;
} else if(amount > 50) {
    plazos = 2;
}

return plazos;
JS;

  /**
   * Data provider: amount => expected plazos
   */
  public static function amountProvider(): array
  {
    return [
      'low'      => [30, 1],
      'just50'   => [50, 1],
      'just51'   => [51, 2],
      'between'  => [75, 2],
      '101'      => [101, 3],
      '151'      => [151, 4],
      '301'      => [301, 6],
      '601'      => [601, 10],
      '999'      => [999, 12],
      '1000'     => [1000, 12],
      '400'      => [400, 6],
    ];
  }

  /**
   * Behavioural test: for each amount, the runtime should return the expected "plazos".
   *
   * @dataProvider amountProvider
   * @runInSeparateProcess
   */
  public function testScriptReturnsExpectedPlazos(int $amount, int $expected): void
  {
    // Build the script: define amount then include original logic.
    $js = "var amount = " . (int)$amount . ";\n" . $this->originalJs;

    // Use JS::run to execute. Use fully-qualified name if needed.
    if (!class_exists(\JS::class) && class_exists(\js4php5\JS::class)) {
      $runner = \js4php5\JS::class;
    } else {
      $runner = \JS::class;
    }

    // Execute the script through the project's runtime API.
    $result = $runner::run($js);

    // The runtime may return numeric values as float (e.g. 1.0). Cast to int for logical equality.
    // This keeps the test robust to numeric representation differences between PHP and the JS runtime.
    $this->assertSame($expected, (int)$result, "amount={$amount} should map to plazos={$expected}");
  }

  /**
   * Quick compile/emission smoke test: ensure Compiler::compile returns a PHP source string
   * that references Runtime:: (we expect Runtime helpers in emitted code).
   *
   * @runInSeparateProcess
   */
  public function testCompileEmitsPhpContainingRuntime(): void
  {
    if (!class_exists(\js4php5\compiler\Compiler::class) && !class_exists(\Compiler::class)) {
      $this->markTestSkipped('Compiler class not available for emission test.');
      return;
    }

    $js = "var amount = 42;\n" . $this->originalJs;

    // Use the FQCN that exists
    $compilerClass = class_exists(\js4php5\compiler\Compiler::class) ? \js4php5\compiler\Compiler::class : \Compiler::class;

    $php = $compilerClass::compile($js);

    $this->assertIsString($php);
    $this->assertNotEmpty($php);
    // Heuristic: emitted code should reference Runtime:: functions (present in generated example).
    $this->assertStringContainsString('Runtime::', $php);
    // And should mention the variable name 'plazos'
    $this->assertStringContainsString('plazos', $php);
  }
}
