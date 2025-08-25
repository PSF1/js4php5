<?php

namespace js4php5\compiler\parser;

class Helpers
{
  /** Marker used in automata to indicate "no mark" */
  public static $FA_NO_MARK = 99999;

  /**
   * Generate a unique state label (prefixed with a letter to avoid numeric-only keys).
   * This function guarantees no repeats within the same process.
   */
  public static function gen_label()
  {
    // Won't return the same number twice. Note that we use state labels
    // for hash keys all over the place. To prevent PHP from doing the
    // wrong thing when we merge such hashes, we tack a letter on the
    // front of the labels.
    static $count = 0;
    $count++;
    return 's' . $count;
  }
}
