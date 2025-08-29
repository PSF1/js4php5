<?php

namespace js4php5\compiler\parser;

class enfa
{
    # Contains a list of labels
    public $states = array();
    # These are hashes with state labels for keys:
    # sub-hash from symbol to label-list
    public $delta = array();
    # label-list
    public $epsilon = array();
    # distinguishing mark
    public $mark = array();

    # Now we can add the initial and final states:
    public $initial = null;
    public $final = null;

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

    function eclose($label_list)
    {
      // Build a set of states using labels as keys to avoid array_count_values warnings
      // Use associative array as a set
      $states = [];
      $queue = [];

      // Initialize set and queue with given labels
      foreach ($label_list as $lbl) {
        $states[$lbl] = true; // Mark label as seen
        $queue[] = $lbl;      // Enqueue label for BFS
      }

      while (!empty($queue)) {
        $s = array_shift($queue);
        if (empty($this->epsilon[$s])) {
          continue;
        }
        foreach ($this->epsilon[$s] as $t) {
          if (!isset($states[$t])) {
            $states[$t] = true; // Add new reachable state
            $queue[] = $t;      // Enqueue it
          }
        }
      }

      return array_keys($states);
    }

    function any_are_final($label_list)
    {
        return in_array($this->final, $label_list);
    }

    function best_mark($label_list)
    {
        $mark = Helpers::$FA_NO_MARK;
        foreach ($label_list as $label) {
            $mark = min($mark, ($this->mark[$label] ?? PHP_INT_MAX));
        }
        return $mark;
    }

    function add_state($label)
    {
        if (isset($this->delta[$label])) {
          throw new \RuntimeException("Trying to add existing state to an NFA.");
        }
        $this->states[] = $label;
        $this->delta[$label] = array();
        $this->epsilon[$label] = array();
        $this->mark[$label] = Helpers::$FA_NO_MARK;
        return $label;
    }

    function add_epsilon($src, $dest)
    {
        $this->epsilon[$src][] = $dest;
    }

    function start_states()
    {
        return $this->eclose(array($this->initial));
    }

    function add_transition($src, $glyph, $dest)
    {
        $lst = &$this->delta[$src];
        if (empty($lst[$glyph])) {
            $lst[$glyph] = array($dest);
        } else {
            $lst[$glyph][] = $dest;
        }
    }

    function step($label_list, $glyph)
    {
        $out = array();
        foreach ($label_list as $label) {
            if (isset($this->delta[$label][$glyph])) {
                $out = array_merge($out, $this->delta[$label][$glyph]);
            }
        }
        return $this->eclose($out);
    }

    function accepting($label_list)
    {
        # Return a set of those glyphs which will not kill the NFA.
        # Assume that any necessary epsilon closure is already done.

        # Note that there is a certain amount of unavoidable cleverness
        # in the algorithm. I don't care the values of $out, so it
        # doesn't matter that they happen also to be some arbitrary
        # transition lists.
        $out = array();
        foreach ($label_list as $label) {
            $out = array_merge($out, $this->delta[$label]);
        }
        return array_keys($out);
    }

    /*
    Now that we have the basics down, I'd like some functions that
    let me make convenient modifications to an NFA. In particular,
    I would like to:

    1: Recognize a particular sequence of glyphs
    2: Accept the union of the current NFA and some other
    3: Perform the Kleene closure
    4: Similar for the common + and ? operators
    5: Accept the concatenation of this and some other NFA.

    Fortunately, these all boil down to a fairly simple set of steps.

    One slightly complicated part is that I'd also like to be able
    to carry these "distinguishing marks" through the system so that
    they can instruct the final PDA on which production matched.

    The other more complicated part is that these production rules are
    really transducers. Each rule has certain parts which must go into
    a parse tree node. It turns out that this is a relatively hard
    problem in the short run, and not necessary for a solution to the
    ultimate goal of getting PHP programs into a "tree-of-lists" structure.
    */

    function recognize($glyph)
    {
        $this->add_transition($this->initial, $glyph, $this->final);
    }

    function plus()
    {
        # Recognize the current NFA one or more times:
        $this->add_epsilon($this->final, $this->initial);
    }

    function hook()
    {
        # Recognize the current NFA zero or one times:
        $this->add_epsilon($this->initial, $this->final);
    }

    function kleene()
    {
        # kleen-star closure over the current NFA:
        $this->hook();
        $this->plus();
    }

    function copy_in($nfa)
    {
        # Used by the union and concatenation operations.
        # Highly magical. Counts on a few things....
        foreach (array('states', 'delta', 'epsilon', 'mark') as $part) {
            $this->$part = array_merge($this->$part, $nfa->$part);
        }
    }

    function determinize()
    {
        # Now I can write the code that converts
        # an NFA into an equivalent DFA.

        $map = new state_set_labeler();
        $start = $this->start_states();
        $queue = array($start);

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
