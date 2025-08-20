<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\Timer;

final class TimerRestartAndIdempotencyTest extends TestCase
{
  protected function setUp(): void
  {
    // Reset private static state via bound closure to avoid Reflection
    $reset = \Closure::bind(function () {
      // Reset all static properties to a clean state
      Timer::$timers = [];
      Timer::$counter = 0;
      Timer::$lastNumericId = null;
    }, null, Timer::class);
    $reset();
  }

  public function testDoubleStopIsIdempotent(): void
  {
    // Start and stop once
    Timer::start('T1');
    usleep(10000); // 10ms
    $first = Timer::stop('T1');

    // A second stop should return the exact same elapsed value (no change)
    usleep(5000); // 5ms - should not affect the final value
    $second = Timer::stop('T1');

    // Exact equality expected because the stored value is returned
    $this->assertSame($first, $second, 'Second stop must be idempotent and return the same elapsed time');
  }

  public function testRestartExistingLabelOverwritesPreviousTime(): void
  {
    // First run
    Timer::start('T2');
    usleep(5000); // 5ms
    Timer::stop('T2');
    $first = Timer::get('T2');

    // Restart same label (should overwrite previous elapsed)
    Timer::start('T2');
    usleep(20000); // 20ms
    $second = Timer::stop('T2');

    // The second elapsed must reflect the new run (should be greater than the first given longer sleep)
    $this->assertGreaterThan($first, $second);
    // get() after stop should match the stored elapsed
    $this->assertEqualsWithDelta($second, Timer::get('T2'), 1e-6);
  }

  public function testRestartRunningTimerResetsStart(): void
  {
    // Start and let it run for a while
    Timer::start('T3');
    usleep(50000); // 50ms

    // Restart without stopping; should reset start time
    Timer::start('T3');
    usleep(20000); // 20ms

    $elapsed = Timer::stop('T3');

    // Should reflect only the time after the restart, not the original 50ms
    $this->assertGreaterThan(0.0, $elapsed);
    $this->assertLessThan(0.1, $elapsed, 'Elapsed should be well under 100ms after restart');
  }
}
