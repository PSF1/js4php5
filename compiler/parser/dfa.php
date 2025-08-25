<?php

namespace js4php5\compiler\parser;

class dfa
{
  /*
  A DFA has a simpler representation than that of an NFA.
  It also has a bit of a different interface.
  */

  /** @var array<int, string> */
  public $states;

  /** @var string */
  public $initial;

  /** @var array<string, bool> */
  public $final;

  /** @var array<string, array<string, string>> */
  public $delta;

  /** @var array<string, int> */
  public $mark;

  /**
   * Modern constructor to initialize DFA structures (replaces legacy dfa()).
   */
  public function __construct()
  {
    // $this->alphabet = []; // Not used
    $this->states  = [];         // Contains a list of labels
    $this->initial = '';         // Set this later.

    // These are hashes with state labels for keys:
    $this->final = [];           // Just a bit for each state
    $this->delta = [];           // sub-hash from symbol to label
    $this->mark  = [];           // distinguishing mark
  }

  /**
   * Backward-compat PHP4-style constructor: call __construct().
   */
  public function dfa()
  {
    $this->__construct();
  }

  /**
   * @param string $label
   * @return string
   */
  public function add_state($label)
  {
    if ($this->has_state($label)) {
      throw new \RuntimeException("Trying to add existing state to a DFA.");
    }
    $this->states[]     = $label;
    $this->final[$label] = false;
    $this->delta[$label] = [];
    $this->mark[$label]  = Helpers::$FA_NO_MARK;
    return $label;
  }

  /**
   * @param string $label
   * @return bool
   */
  public function has_state($label)
  {
    return isset($this->delta[$label]);
  }

  /**
   * @param string $src
   * @param string $glyph
   * @param string $dest
   * @return void
   */
  public function add_transition($src, $glyph, $dest)
  {
    $this->delta[$src][$glyph] = $dest;
  }

  /**
   * @param string $label
   * @param string $glyph
   * @return string|null
   */
  public function step($label, $glyph)
  {
    // Avoid error suppression and notices on unknown keys
    return $this->delta[$label][$glyph] ?? null;
  }

  /**
   * @param string $label
   * @return array<int, string>
   */
  public function accepting($label)
  {
    // Return the outgoing symbol set from a state (empty if none/unknown)
    return isset($this->delta[$label]) ? array_keys($this->delta[$label]) : [];
  }

  public function minimize()
  {
    /*
    We'll use the table-filling algorithm to find pairs of
    distinguishable states. When that algorithm is done, any states
    not distinguishable are equivalent. We'll return a new DFA.
    */

    $map = $this->indistinguishable_state_map($this->table_fill());
    $dist = [];
    foreach ($map as $p => $q) {
      $dist[$q] = $q;
    }

    $dfa = new dfa();
    foreach ($dist as $p) {
      $dfa->add_state($p);
    }
    foreach ($dist as $p) {
      foreach ($this->delta[$p] as $glyph => $q) {
        $dfa->add_transition($p, $glyph, $map[$q]);
      }
      $dfa->final[$p] = $this->final[$p];
      $dfa->mark[$p]  = $this->mark[$p];
    }
    $dfa->initial = $map[$this->initial];

    return $dfa;
  }

  public function indistinguishable_state_map($table)
  {
    // Assumes that $table is filled according to the table filling algorithm.
    $map = [];
    $set = new set($this->states);
    while ($set->count()) {
      $p = $set->one();
      foreach ($set->all() as $q) {
        if (!$table->differ($p, $q)) {
          $map[$q] = $p;
          $set->del($q);
        }
      }
    }
    return $map;
  }

  public function table_fill()
  {
    /*
    We use a slight modification of the standard base case:
    Two states are automatically distinguishable if their marks differ.
    */

    // Base Case:
    $table = new distinguishing_table();

    foreach ($this->states as $s1) {
      foreach ($this->states as $s2) {
        if ($this->mark[$s1] != $this->mark[$s2]) {
          $table->distinguish($s1, $s2);
        }
      }
    }

    // Induction:
    do { /* nothing */ } while (!$this->filling_round($table));

    return $table;
  }

  public function filling_round(&$table)
  {
    $done = true;

    foreach ($this->states as $s1) {
      foreach ($this->states as $s2) {
        if ($s1 == $s2) {
          continue;
        }
        if (!$table->differ($s1, $s2)) {
          // Try to find a reason why the two states differ.
          $different = $this->compare_states($s1, $s2, $table);
          if ($different) {
            $table->distinguish($s1, $s2);
            $done = false;
            break;
          }
        }
      }
    }
    return $done;
  }

  public function compare_states($p, $q, $table)
  {
    $sigma = array_unique(array_merge($this->accepting($p), $this->accepting($q)));
    if ($p == $q) {
      return false;
    }

    foreach ($sigma as $glyph) {
      $p1 = $this->step($p, $glyph);
      $q1 = $this->step($q, $glyph);
      if (!($p1 && $q1) || $table->differ($p1, $q1)) {
        return true;
      }
    }

    return false;
  }
}
