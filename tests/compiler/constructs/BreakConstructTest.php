<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\c_break;
use js4php5\compiler\constructs\c_source;

final class BreakConstructTest extends TestCase
{
  protected function setUp(): void
  {
    // Reset real c_source state (class from the library)
    c_source::$nest = 0;
    c_source::$labels = [];
  }

  public function testBreakOutsideLoopEmitsErrorBanner(): void
  {
    c_source::$nest = 0;
    $node = new c_break(';');
    $code = $node->emit();

    $this->assertStringContainsString('ERROR: break outside of a loop', $code);
  }

  public function testUnlabeledBreakEmitsPlainBreak(): void
  {
    c_source::$nest = 1;
    $node = new c_break(';');
    $code = $node->emit();

    $this->assertSame("break;\n", $code);
  }

  public function testLabeledBreakComputesDepth(): void
  {
    c_source::$nest = 3;
    c_source::$labels = ['myLabel' => 1]; // label defined at depth 1

    $node = new c_break('myLabel');
    $code = $node->emit();

    // depth = nest - labels[label] = 3 - 1 = 2
    $this->assertSame("break 2;\n", $code);
  }
}
