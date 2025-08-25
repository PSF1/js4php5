<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\c_break;
use js4php5\compiler\constructs\c_continue;
use js4php5\compiler\constructs\c_source;

final class BreakContinueInteractionTest extends TestCase
{
  protected function setUp(): void
  {
    // Reset real c_source state
    c_source::$nest = 0;
    c_source::$labels = [];
  }

  public function testBothComputeDepthFromSameLabelMapping(): void
  {
    // At nesting level 4, label "L1" was defined at level 2
    c_source::$nest = 4;
    c_source::$labels = ['L1' => 2];

    // break to L1 => depth = 4 - 2 = 2
    $br = new c_break('L1');
    $this->assertSame("break 2;\n", $br->emit());

    // continue to L1 => depth = 4 - 2 = 2
    $ct = new c_continue('L1');
    $this->assertSame("continue 2;\n", $ct->emit());
  }

  public function testUnknownLabelFallsBackGracefully(): void
  {
    c_source::$nest = 3;
    c_source::$labels = []; // unknown label

    $br = new c_break('Missing');
    $ct = new c_continue('Missing');

    // Fallback to unlabeled behavior
    $this->assertSame("break;\n", $br->emit());
    $this->assertSame("continue;\n", $ct->emit());
  }

  public function testDepthIsClampedAtLeastOneForBoth(): void
  {
    // If a mismatch yields <= 0, both should clamp to 1
    c_source::$nest = 2;
    c_source::$labels = ['L' => 2]; // 2 - 2 = 0 -> clamp to 1

    $br = new c_break('L');
    $ct = new c_continue('L');

    $this->assertSame("break 1;\n", $br->emit());
    $this->assertSame("continue 1;\n", $ct->emit());
  }
}
