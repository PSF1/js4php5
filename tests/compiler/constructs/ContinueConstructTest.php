<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\c_continue;
use js4php5\compiler\constructs\c_source;

final class ContinueConstructTest extends TestCase
{
  protected function setUp(): void
  {
    // Reset real c_source state (class from the library)
    c_source::$nest = 0;
    c_source::$labels = [];
  }

  public function testContinueOutsideLoopEmitsErrorBanner(): void
  {
    // Not inside any loop/switch
    c_source::$nest = 0;

    $node = new c_continue(';'); // unlabeled continue
    $code = $node->emit();

    $this->assertStringContainsString('ERROR: continue outside of a loop', $code);
  }

  public function testUnlabeledContinueEmitsPlainContinue(): void
  {
    c_source::$nest = 1;

    $node = new c_continue(';'); // unlabeled
    $code = $node->emit();

    $this->assertSame("continue;\n", $code);
  }

  public function testLabeledContinueComputesDepth(): void
  {
    // Suppose we are at nesting level 3 and label is at level 1
    c_source::$nest = 3;
    c_source::$labels = ['myLabel' => 1];

    $node = new c_continue('myLabel');
    $code = $node->emit();

    // depth = nest - labels[label] = 3 - 1 = 2
    $this->assertSame("continue 2;\n", $code);
  }

  public function testUnknownLabelFallsBackToPlainContinue(): void
  {
    c_source::$nest = 2;
    c_source::$labels = []; // label not defined

    $node = new c_continue('missing');
    $code = $node->emit();

    $this->assertSame("continue;\n", $code);
  }

  public function testDepthIsClampedToAtLeastOne(): void
  {
    // If computation yields 0 or negative, code should clamp to "continue 1;"
    c_source::$nest = 2;
    c_source::$labels = ['L' => 2]; // 2 - 2 = 0 -> clamp to 1

    $node = new c_continue('L');
    $code = $node->emit();

    $this->assertSame("continue 1;\n", $code);
  }
}
