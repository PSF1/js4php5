<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\Timer;

final class TimerEdgeCasesTest extends TestCase
{
  protected function setUp(): void
  {
    // Reset private static state without Reflection
    $reset = \Closure::bind(function () {
      Timer::$timers = [];
      Timer::$counter = 0;
    }, null, Timer::class);
    $reset();
  }

  public function testAutoIdStopWithoutLabelUsesLastId(): void
  {
    // According to the docblock, stop(null) should use the last used numeric ID.
    // Current implementation uses the current counter (off by one) and likely returns 0 -> test will FAIL until fixed.
    Timer::start();       // implicit label 0, counter becomes 1
    usleep(5000);
    $elapsed = Timer::stop(); // should use label 0
    $this->assertGreaterThan(0.0, $elapsed);
  }

  public function testOutputWithNumericLabelDoesNotThrow(): void
  {
    // Starting without label creates an integer key (0). strlen/int with PHP 8 will TypeError.
    // This test will FAIL until output() casts keys to string.
    Timer::start();
    usleep(1000);
    Timer::stop();

    // If it doesn't throw, expect the label "0" to appear
    $this->expectOutputRegex('/\b0\b/');
    Timer::output(false);
  }
}
