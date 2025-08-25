<?php

namespace js4php5\compiler\parser;

use js4php5\compiler\lexer\Lexer;

//TODO: Fix known bug whre parser will get stuck on a script ending with a + (does not recognize bad syntax).
//Can be tested with a script containing only '+'.
abstract class Parser
{
  /** @var array */
  protected $pda;

  /** @var array */
  protected $action;

  /** @var array */
  protected $delta;

  /** @var array */
  protected $start;

  public function __construct($pda)
  {
    $this->pda    = $pda;
    $this->action = $pda['action'];
    $this->start  = $pda['start'];
    $this->delta  = $pda['delta'];
  }

  public function report()
  {
    // pr($this->action);
    // print_r($this->start);
    foreach ($this->delta as $label => $d) {
      echo "<h3>State $label</h3>";
      foreach ($d as $glyph => $step) {
        echo $glyph . " -&gt; " . implode(':', $step) . "<br>";
      }
    }
  }

  /**
   * @param string              $symbol
   * @param Lexer               $lex
   * @param null|ParserStrategy $strategy
   *
   * @throws parse_error
   */
  public function parse($symbol, Lexer $lex, ParserStrategy $strategy = null)
  {
    $strategy = $strategy ?? new DefaultParserStrategy();

    $stack = array();
    $tos = $this->frame($symbol);
    $token = $lex->next();

    while (true) {
      $step = $this->getStep($tos->state, $token->getType());

      switch ($step[0]) {
        case 'go':
          $tos->shift($token->getText());
          $tos->state = $step[1];
          $token = $lex->next();
          break;

        case 'do':
          $semantic = $this->reduce($step[1], $tos->semantic());
          if (empty($stack)) {
            $strategy->assertDone($token, $lex);
            return $semantic;
          } else {
            $tos = array_pop($stack);
            $tos->shift($semantic);
          }
          break;

        case 'push':
          $tos->state = $step[2];
          $stack[] = $tos;
          $tos = $this->frame($step[1]);
          break;

        case 'fold':
          $tos->fold($this->reduce($step[1], $tos->semantic()));
          $tos->state = $step[2];
          break;

        case 'error':
          $stack[] = $tos;
          $strategy->stuck($token, $lex, $stack);
          break;

        default:
          throw new parse_error("Impossible. Bad PDA has $step[0] instruction.");
      }
    }
    // return statement is in case 'do'
  }

  public function frame($symbol)
  {
    return new ParseStackFrame($symbol, $this->start[$symbol]);
  }

  public function getStep($label, $glyph)
  {
    if (!isset($this->delta[$label]) || !is_array($this->delta[$label])) {
      return array('error');
    }
    $delta = $this->delta[$label];
    if (isset($delta[$glyph])) {
      return $delta[$glyph];
    }
    if (isset($delta['[default]'])) {
      return $delta['[default]'];
    }
    return array('error');
  }

  abstract public function reduce($action, $tokens);
}
