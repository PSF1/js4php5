<?php

namespace js4php5\compiler\constructs;

class c_break extends BaseConstruct
{
  /** @var string Break target label; ';' means unlabeled break */
  public $label;

  /**
   * @param string $label
   */
  function __construct($label)
  {
    // Normalize to string to avoid passing null/ints around
    $this->label = (string) $label;
  }

  /**
   * Emit break statement. Uses c_source::$nest and c_source::$labels provided by the parser.
   *
   * Notes:
   * - If not inside a loop/switch (nest <= 0), returns an error banner (legacy behavior).
   * - If unlabeled (';'), emits "break;".
   * - If labeled and the label exists, computes depth = nest - labels[label] (clamped to >= 1).
   * - If labeled but the label does not exist, falls back to unlabeled "break;" to avoid notices.
   */
  function emit($unusedParameter = false)
  {
    // Not inside a loop/switch: keep legacy error banner
    if (c_source::$nest <= 0) {
      return "ERROR: break outside of a loop\n*************************\n\n";
    }

    // Unlabeled break
    if ($this->label === ';') {
      return "break;\n";
    }

    // Labeled break: check that the label exists to avoid undefined index notices
    if (isset(c_source::$labels[$this->label])) {
      $depth = (int) (c_source::$nest - c_source::$labels[$this->label]);
      // Clamp to minimum of 1 to avoid generating "break 0;"
      if ($depth < 1) {
        $depth = 1;
      }
      return "break $depth;\n";
    }

    // Unknown label: fallback to unlabeled break to avoid runtime notices
    return "break;\n";
  }
}
