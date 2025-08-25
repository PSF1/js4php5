<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\parser;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\parser\Helpers;

final class HelpersTest extends TestCase
{
  public function testFaNoMarkIsIntegerAndHasExpectedValue(): void
  {
    $this->assertIsInt(Helpers::$FA_NO_MARK);
    $this->assertSame(99999, Helpers::$FA_NO_MARK);
  }

  public function testGenLabelProducesUniqueSequentialLikeLabels(): void
  {
    // We cannot assume a specific starting value because other tests may have called gen_label().
    $l1 = Helpers::gen_label();
    $l2 = Helpers::gen_label();

    // Should look like 's<number>'
    $this->assertMatchesRegularExpression('/^s\d+$/', $l1);
    $this->assertMatchesRegularExpression('/^s\d+$/', $l2);

    // Two consecutive calls must not be equal
    $this->assertNotSame($l1, $l2);
  }

  public function testGenLabelKeepsUniquenessAcrossSeveralCalls(): void
  {
    $labels = [];
    for ($i = 0; $i < 5; $i++) {
      $labels[] = Helpers::gen_label();
    }
    $this->assertSame($labels, array_values(array_unique($labels)), 'gen_label() must not repeat labels in sequence');
  }
}
