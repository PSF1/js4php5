<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Integration test for the script whose compiled PHP is shown in the example.
 *
 * We run several amount values to ensure the branching (amount < 3711.18) works as expected.
 */
final class GeneratedScriptEquivalence2Test extends TestCase
{
  private string $originalJs = <<<'JS'
var plazos = 1;

// < 4320$usd
if (amount < 3711.18) {
  plazos = 12;
} else {
  plazos = 40;
}

return plazos;
JS;

  public static function amountProvider(): array
  {
    return [
      'much less' => [1000, 12],
      'just below' => [3711.17, 12],
      'equal' => [3711.18, 40],
      'just above' => [3711.19, 40],
      'much larger' => [5000, 40],
    ];
  }

  /**
   * For each amount, the script should return the expected plazos.
   *
   * @dataProvider amountProvider
   * @runInSeparateProcess
   */
  public function testScriptReturnsExpectedPlazosForFloatComparison($amount, int $expected): void
  {
    // Build script: define amount then append original logic.
    $js = "var amount = " . (float)$amount . ";\n" . $this->originalJs;

    // Choose available JS runner class (global JS or namespaced js4php5\JS).
    if (!class_exists(\JS::class) && class_exists(\js4php5\JS::class)) {
      $runner = \js4php5\JS::class;
    } else {
      $runner = \JS::class;
    }

    $result = $runner::run($js);

    // The runtime may return numbers as float (1.0 etc). Cast to int to compare logical integer result.
    $this->assertSame($expected, (int)$result, "amount={$amount} should map to plazos={$expected}");
  }

  /**
   * Quick compile/emission smoke test to ensure compile() produces PHP source containing Runtime::cmp or Runtime:: references.
   *
   * @runInSeparateProcess
   */
  public function testCompileEmitsPhpContainingRuntimeCmp(): void
  {
    if (!class_exists(\js4php5\compiler\Compiler::class) && !class_exists(\Compiler::class)) {
      $this->markTestSkipped('Compiler class not available for emission test.');
      return;
    }

    $js = "var amount = 1234;\n" . $this->originalJs;
    $compilerClass = class_exists(\js4php5\compiler\Compiler::class) ? \js4php5\compiler\Compiler::class : \Compiler::class;

    $php = $compilerClass::compile($js);

    $this->assertIsString($php);
    $this->assertNotEmpty($php);

    // The generated example used Runtime::cmp(..., 1) for comparison; accept either cmp or general Runtime:: presence.
    $this->assertTrue(strpos($php, 'Runtime::cmp') !== false || strpos($php, 'Runtime::') !== false, 'Compiled PHP should reference Runtime::cmp or Runtime::');
  }
}
