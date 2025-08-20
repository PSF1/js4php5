<?php

namespace js4php5;

/**
 * Simple execution timer class to aid in js4php5 development.
 *
 * To be removed at a later date. Or moved to its own project with enhancements.
 *
 * Ideas:
 * - Implement an 'add' function where chunks of code can be incrementally timed, like stopping and starting a stopwatch.
 */
class Timer
{
  /**
   * Each entry stores explicit state:
   * - 'start'   => float|null (start timestamp from microtime(true) while running)
   * - 'elapsed' => float|null (final elapsed seconds after stop)
   *
   * Keys can be int or string labels.
   * @var array<int|string, array{start: float|null, elapsed: float|null}>
   */
  private static array $timers = [];

  /**
   * Auto-incrementing counter for numeric labels.
   */
  private static int $counter = 0;

  /**
   * Tracks the last auto-assigned numeric label (for stop/get when $label === null).
   */
  private static ?int $lastNumericId = null;

  /**
   * Start a timer. Erases the previous time if a previously-timed label is used.
   *
   * @param string|int|null $label Timer ID. If none given, an incremental numeric ID will be used.
   */
  public static function start(string|int|null $label = null): void
  {
    if ($label === null) {
      // Auto-assign an incremental numeric label
      $label = static::$counter++;
      // Remember the last auto-assigned numeric ID
      static::$lastNumericId = $label;
    } else {
      // If a numeric label is explicitly provided, track it as last numeric ID
      if (is_int($label)) {
        static::$lastNumericId = $label;
      }
    }

    // Initialize/overwrite entry with explicit state
    static::$timers[$label] = [
      'start'   => microtime(true), // running
      'elapsed' => null,            // not yet stopped
    ];
  }

  /**
   * Stop a timer. Return the time taken in seconds.
   *
   * @param string|int|null $label Timer ID. If none given, the last used numeric ID will be used.
   */
  public static function stop(string|int|null $label = null): float
  {
    $label = static::resolveLabel($label);

    if (!isset(static::$timers[$label])) {
      // Not found
      return 0.0;
    }

    $entry = &static::$timers[$label];

    // If already stopped, return stored elapsed time
    if ($entry['elapsed'] !== null) {
      return $entry['elapsed'];
    }

    // If running, compute elapsed and finalize
    if ($entry['start'] !== null) {
      $entry['elapsed'] = microtime(true) - $entry['start'];
      $entry['start'] = null; // mark as stopped
      return $entry['elapsed'];
    }

    // Fallback (shouldn't happen): return 0.0
    return 0.0;
  }

  /**
   * Get current or saved execution time.
   *
   * If a timer has been stopped, it will return the recorded time.
   * If a timer has NOT been stopped, it will return the execution time so far, but will not stop the timer.
   *
   * @param string|int|null $label Timer ID. If none given, the last used numeric ID will be used.
   */
  public static function get(string|int|null $label = null): float
  {
    $label = static::resolveLabel($label);

    if (!isset(static::$timers[$label])) {
      // Not found
      return 0.0;
    }

    $entry = static::$timers[$label];

    // If already stopped, return recorded elapsed time
    if ($entry['elapsed'] !== null) {
      return $entry['elapsed'];
    }

    // If running, compute "so far" without stopping
    if ($entry['start'] !== null) {
      return microtime(true) - $entry['start'];
    }

    // Fallback (shouldn't happen): return 0.0
    return 0.0;
  }

  /**
   * Output a simple list of all stored times and their labels.
   *
   * @param bool $ms If true, output in milliseconds (e.g. "51 ms"); if false, output in seconds (e.g. "0.0519234756 s").
   */
  public static function output(bool $ms = false): void
  {
    echo '<hr><h2>Times:</h2><pre>';

    $maxkeylen = 0;
    foreach (static::$timers as $key => $_entry) {
      // Ensure we measure string length; integers will be cast safely
      $keylen = strlen((string)$key);
      if ($keylen > $maxkeylen) {
        $maxkeylen = $keylen;
      }
    }

    foreach (static::$timers as $key => $_entry) {
      // Pad label for alignment; cast to string for consistency
      echo str_pad((string)$key, $maxkeylen, '.') . ': ';

      $value = static::get($key);
      echo $ms
        ? round($value * 1000) . " ms\n"
        : $value . " s\n";
    }

    echo '</pre><hr>';
  }

  /**
   * Convenience: output in milliseconds.
   */
  public static function output_ms(): void
  {
    static::output(true);
  }

  /**
   * Resolve the effective label for operations where $label may be null.
   * - If null: use last auto-assigned numeric ID if available; otherwise previous counter if any, else 0.
   * - If non-null: return as-is.
   *
   * @param string|int|null $label
   * @return string|int
   */
  private static function resolveLabel(string|int|null $label): string|int
  {
    if ($label !== null) {
      return $label;
    }

    if (static::$lastNumericId !== null) {
      return static::$lastNumericId;
    }

    return (static::$counter > 0) ? (static::$counter - 1) : 0;
  }
}
