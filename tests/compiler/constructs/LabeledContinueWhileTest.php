<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\Integration;

use PHPUnit\Framework\TestCase;
use js4php5\JS;

final class LabeledContinueWhileTest extends TestCase
{
  public function testCompileWhileWithLabeledContinueContainsContinueStatement(): void
  {
    // Simple labeled loop that continues to the label
    $script = <<<JS
var i = 0;
label1: while (i < 3) {
    i = i + 1;
    continue label1;
}
JS;

    // Compile only; do not eval. We just verify the emitted PHP contains a continue with depth.
    $php = JS::compileScript($script, 'labeled-continue-while');

    // Expect at least one "continue <depth>;" in generated code.
    // Allow depth to be any positive integer.
    $this->assertMatchesRegularExpression('/continue\s+\d+;/', $php);

    // Optionally check that the label name appears nearby (not strictly necessary)
    $this->assertStringContainsString('continue', $php);
  }
}
