<?php

namespace js4php5\compiler\parser;

class enfa
{
  /** @var array<int, string> */
  public $states;

  /** @var array<string, array<string, array<int, string>>> */
  public $delta;

  /** @var array<string, array<int, string>> */
  public $epsilon;

  /** @var array<string, int> */
  public $mark;

  /** @var string */
  public $initial;

  /** @var string */
  public $final;

  /**
   * Modern constructor (replaces legacy enfa()).
   * Initializes structures and creates initial/final states.
   */
  public function __construct()
  {
    // $this->alphabet = []; // Not used
    $this->states  = [];
    $this->delta   = [];
    $this->epsilon = [];
    $this->mark    = [];

    // Create initial and final states
    $this->initial = $this->add_state(Helpers::gen_label());
    $this->final   = $this->add_state(Helpers::gen_label());
  }

  /**
   * Backward-compat PHP4-style constructor: call __construct().
   */
  public function enfa()
  {
    $this->__construct();
  }

  /**
   * Compute epsilon-closure of a set of labels.
   *
   * @param array<int, string> $label_list
   * @return array<int, string>
   */
  public function eclose($label_list)
  {
    $states = array_count_values($label_list);
    $queue = array_keys($states);
    while (count($queue) > 0) {
      $s = array_shift($queue);
      foreach ($this->epsilon[$s] as $t) {
        if (!isset($states[$t])) {
          $states[$t] = true;
          $queue[] = $t;
        }
      }
    }
    return array_keys($states);
  }

  /**
   * @param array<int, string> $label_list
   * @return bool
   */
  public function any_are_final($label_list)
  {
    return in_array($this->final, $label_list, true);
  }

  /**
   * @param array<int, string> $label_list
   * @return int
   */
  public function best_mark($label_list)
  {
    $mark = Helpers::$FA_NO_MARK;
    foreach ($label_list as $label) {
      $mark = min($mark, $this->mark[$label]);
    }
    return $mark;
  }

  /**
   * @param string $label
   * @return string
   */
  public function add_state($label)
  {
    if (isset($this->delta[$label])) {
      throw new \RuntimeException("Trying to add existing state to an NFA.");
    }
    $this->states[]      = $label;
    $this->delta[$label] = [];
    $this->epsilon[$label] = [];
    // Use Helpers::$FA_NO_MARK (not bare constant) to avoid undefined name
    $this->mark[$label]  = Helpers::$FA_NO_MARK;
    return $label;
  }

  /**
   * @param string $src
   * @param string $dest
   * @return void
   */
  public function add_epsilon($src, $dest)
  {
    $this->epsilon[$src][] = $dest;
  }

  /**
   * @return array<int, string>
   */
  public function start_states()
  {
    return $this->eclose([$this->initial]);
  }

  /**
   * @param string $src
   * @param string $glyph
   * @param string $dest
   * @return void
   */
  public function add_transition($src, $glyph, $dest)
  {
    // Append destination to the list for this glyph
    if (empty($this->delta[$src][$glyph])) {
      $this->delta[$src][$glyph] = [$dest];
    } else {
      $this->delta[$src][$glyph][] = $dest;
    }
  }

  /**
   * @param array<int, string> $label_list
   * @param string             $glyph
   * @return array<int, string> epsilon-closure of the union of all transitions
   */
  public function step($label_list, $glyph)
  {
    $out = [];
    foreach ($label_list as $label) {
      if (isset($this->delta[$label][$glyph])) {
        $out = array_merge($out, $this->delta[$label][$glyph]);
      }
    }
    return $this->eclose($out);
  }

  /**
   * Return a set of symbols (glyphs) that do not kill the NFA from any of the given states.
   *
   * @param array<int, string> $label_list
   * @return array<int, string>
   */
  public function accepting($label_list)
  {
    $out = [];
    foreach ($label_list as $label) {
      $out = array_merge($out, $this->delta[$label] ?? []);
    }
    return array_keys($out);
  }

  public function recognize($glyph)
  {
    $this->add_transition($this->initial, $glyph, $this->final);
  }

  public function plus()
  {
    // Recognize the current NFA one or more times:
    $this->add_epsilon($this->final, $this->initial);
  }

  public function hook()
  {
    // Recognize the current NFA zero or one times:
    $this->add_epsilon($this->initial, $this->final);
  }

  public function kleene()
  {
    // Kleene-star closure over the current NFA:
    $this->hook();
    $this->plus();
  }

  public function copy_in($nfa)
  {
    // Used by the union and concatenation operations. Highly magical.
    foreach (['states', 'delta', 'epsilon', 'mark'] as $part) {
      $this->$part = array_merge($this->$part, $nfa->$part);
    }
  }

  public function determinize()
  {
    // Convert the eNFA into an equivalent DFA.
    $map = new state_set_labeler();
    $start = $this->start_states();
    $queue = [$start];

    $dfa = new dfa();
    $i = $map->label($start);
    $dfa->add_state($i);
    $dfa->initial = $i;
    $dfa->mark[$i] = $this->best_mark($start);

    while (count($queue) > 0) {
      $set = array_shift($queue);
      $label = $map->label($set);
      foreach ($this->accepting($set) as $glyph) {
        $dest = $this->step($set, $glyph);
        $dest_label = $map->label($dest);
        if (!$dfa->has_state($dest_label)) {
          $dfa->add_state($dest_label);
          $dfa->mark[$dest_label] = $this->best_mark($dest);
          $queue[] = $dest;
        }
        $dfa->add_transition($label, $glyph, $dest_label);
      }
      if ($this->any_are_final($set)) {
        $dfa->final[$label] = true;
      }
    }

    return $dfa;
  }
}
