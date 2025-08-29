<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Integration test that runs a small JS program with helper functions.
 *
 * The JS program:
 *  - builds an array [1..n]
 *  - sums its elements
 *  - multiplies the sum by n
 *
 * Expected: compute(5) => (1+2+3+4+5)*5 = 15*5 = 75
 */
final class ComplexCalculationTest extends TestCase
{
  private string $complexJs = <<<'JS'
function mul(a, b) {
  return a * b;
}

function makeArray(n) {
  var a = [];
  for (var i = 0; i < n; i++) {
    a[i] = i + 1; // fill array with 1..n
  }
  return a;
}

function sumArray(arr) {
  var s = 0;
  // Use array length property and index access (avoids push())
  for (var j = 0; j < arr.length; j++) {
    s = s + arr[j];
  }
  return s;
}

function compute(n) {
  var a = makeArray(n);
  var total = sumArray(a);
  return mul(total, n);
}

compute(ARG_N); // ARG_N will be replaced by test
JS;

  public static function provider(): array
  {
    // n => expected result
    return [
      'n=3' => [3, 18],  // (1+2+3)=6 * 3 = 18
      'n=5' => [5, 75],
      'n=1' => [1, 1],
      'n=10' => [10, 550], // sum 1..10 = 55 * 10 = 550
    ];
  }

  /**
   * Execute the generated JS for different n values and assert expected numeric result.
   *
   * @dataProvider provider
   * @runInSeparateProcess
   */
  public function testComplexComputationReturnsExpectedValue(int $n, int $expected): void
  {
    // Replace placeholder with actual argument
    $js = str_replace('ARG_N', (string)$n, $this->complexJs);

    // Select available JS runner: global JS or namespaced js4php5\JS
    if (!class_exists(\JS::class) && class_exists(\js4php5\JS::class)) {
      $runner = \js4php5\JS::class;
    } else {
      $runner = \JS::class;
    }

    $result = $runner::run($js);

    // Runtime may return numeric values as float (e.g. 75.0). Compare as integer for logical equality.
    $this->assertSame($expected, (int)$result, "n={$n} should map to {$expected}");
  }

  /**
   * Check that the compiler emits some PHP code that references Runtime:: (basic smoke test).
   *
   * @runInSeparateProcess
   */
  public function testCompileEmitsPhp(): void
  {
    if (!class_exists(\js4php5\compiler\Compiler::class) && !class_exists(\Compiler::class)) {
      $this->markTestSkipped('Compiler not present');
      return;
    }

    $sampleJs = str_replace('ARG_N', '5', $this->complexJs);
    $compilerClass = class_exists(\js4php5\compiler\Compiler::class) ? \js4php5\compiler\Compiler::class : \Compiler::class;
    $php = $compilerClass::compile($sampleJs);

    $this->assertIsString($php);
    $this->assertNotEmpty($php);
    $this->assertStringContainsString('Runtime::', $php);
  }
}
