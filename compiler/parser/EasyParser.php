<?php

namespace js4php5\compiler\parser;

use js4php5\compiler\jsly;
use js4php5\compiler\lexer\Lexer;
use js4php5\jsc\js_program;

class EasyParser extends Parser
{
  /** @var array<string, string> */
  private $call;

  /** @var ParserStrategy */
  private $strategy;

  /** @var mixed Public to avoid dynamic property deprecation (used by parser pipeline) */
  public $start = null;

  function __construct($pda, $strategy = null)
  {
    parent::__construct($pda);
    // Copy action table from parent parser into our local dispatch table
    $this->call = $this->action;
    $this->strategy = ($strategy ? $strategy : new DefaultParserStrategy());
  }

  /**
   * Reduce handler: dispatch to jsly::$method for the given action.
   *
   * @param string $action
   * @param array  $tokens
   * @return mixed
   */
  function reduce($action, $tokens)
  {
    $call = $this->call[$action];
    return jsly::$call($tokens);
//    $call = $this->call[$action];
//
//    // Forma 1: array "plano" de items (objetos/valores)
//    $outer = is_array($tokens) ? $tokens : [$tokens];
//
//    try {
//      // La mayoría de handlers de jsly esperan esta forma
//      return jsly::$call($outer);
//    } catch (\Throwable $e) {
//      // Forma 2: array de arrays (cada item envuelto),
//      // para handlers que indexan $tokens[n][m]
//      $wrapped = [];
//      foreach ($outer as $t) {
//        $wrapped[] = is_array($t) ? $t : [$t];
//      }
//      return jsly::$call($wrapped);
//    }
  }

  /**
   * @param string              $symbol
   * @param Lexer               $lex
   * @param null|ParserStrategy $strategy
   *
   * @return js_program
   *
   * @throws parse_error
   */
  public function parse($symbol, Lexer $lex, ParserStrategy $strategy = null)
  {
    // Delegate to parent with our default strategy unless one is explicitly provided
    return parent::parse($symbol, $lex, $strategy ?? $this->strategy);
  }
}
