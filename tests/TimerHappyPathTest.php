<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\Timer;

final class TimerHappyPathTest extends TestCase
{
  protected function setUp(): void
  {
    // Reset private static state without Reflection (avoids deprecation issues)
    $reset = \Closure::bind(function () {
      // Access private static props in Timer
      Timer::$timers = [];   // Clear all timers
      Timer::$counter = 0;   // Reset counter
    }, null, Timer::class);
    $reset();
  }

  public function testExplicitLabelStartGetStop(): void
  {
    // Start with explicit label
    Timer::start('L1');

    // Sleep a bit to accumulate time
    usleep(10000); // 10ms

    // get() while running should be > 0
    $running = Timer::get('L1');
    $this->assertGreaterThan(0.0, $running);

    // stop() returns final elapsed time, should be >= running
    $stopped = Timer::stop('L1');
    $this->assertGreaterThanOrEqual($running, $stopped);

    // get() after stop returns the same stored value (within a small tolerance)
    $afterStop = Timer::get('L1');
    $this->assertEqualsWithDelta($stopped, $afterStop, 1e-6);
  }

  public function testGetUnknownLabelReturnsZero(): void
  {
    // No such label; should return 0
    $this->assertSame(0.0, Timer::get('nope'));
  }

  public function testOutputWithStringLabelDoesNotThrowAndShowsSeconds(): void
  {
    // Using a string label avoids integer-key pitfalls
    Timer::start('A');
    usleep(2000); // 2ms
    Timer::stop('A');

    // Capture output; should include label and ' s'
    $this->expectOutputRegex('/Times:/i');
    $this->expectOutputRegex('/A/');
    $this->expectOutputRegex('/\s+s\s*/'); // ends with " s"
    Timer::output(false);
  }

  public function testOutputMsShowsMilliseconds(): void
  {
    Timer::start('B');
    usleep(20000); // 20ms
    Timer::stop('B');

    // Expect " ms" in the output
    $this->expectOutputRegex('/\bms\b/');
    Timer::output_ms();
  }
}
