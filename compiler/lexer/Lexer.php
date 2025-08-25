<?php
/**
 * js4php5 Lexer - Reads in JavaScript source and outputs a tokenized result.
 *
 * Based on parser included with j4p5 which is reportedly from MetaPHP, a project abandoned in 2004.
 */

namespace js4php5\compiler\lexer;

use js4php5\compiler\jsly;

class Lexer
{
  /** @var array<string, array<int, array>> */
  private $pattern;

  /** @var string */
  private $state;

  /** @var mixed */
  private $initContext;

  /** @var mixed */
  private $context;

  /** @var Stream|null */
  private $stream = null;

  /** @var array<string, string> */
  private $megaRegexp = [];

  public function __construct($init_context, $lexerPatterns = null)
  {
    $this->pattern = $lexerPatterns ? $lexerPatterns : array('INITIAL' => array());
    $this->state = 'INITIAL';
    $this->initContext = $init_context;
    $this->context = $init_context;
  }

  /**
   * @param string $string String to be tokenized.
   *
   * @return void
   */
  public function start($string)
  {
    $this->context = $this->initContext;
    $this->stream = new Stream($string);
    $this->megaRegexp = [];

    foreach ($this->pattern as $key => $details) {
      $s = '';
      foreach ((array)$details as $pattern) {
        if ($s !== '') {
          $s .= '|';
        }
        // $pattern[0] debe contener una subexpresión (posiblemente con sus propios paréntesis)
        $s .= $pattern[0];
      }
      // Si no hay patrones para este estado, construimos una regex que nunca casa
      $this->megaRegexp[$key] = $s !== '' ? '(' . $s . ')' : '(?!)';
    }
  }

  /**
   * @return Token
   * @throws LexerException
   */
  public function next()
  {
    // 1) Si no se llamó a start()
    if ($this->stream === null) {
      throw new LexerException('Lexer has not been started');
    }

    // 2) Si el estado actual no existe en los patrones o no se construyó su mega-regex
    if (!isset($this->pattern[$this->state]) || !is_array($this->pattern[$this->state]) || !isset($this->megaRegexp[$this->state])) {
      throw new LexerException("No lexer state called '{$this->state}'");
    }

    $start = $this->stream->pos();

    if ($match = $this->stream->test($this->megaRegexp[$this->state])) {
      $text = $match[0];
      $tmp = array_flip($match);
      if (!isset($tmp[$text])) {
        return $this->stream->defaultRule();
      }
      $index = $tmp[$text] - 1;

      if (!isset($this->pattern[$this->state][$index])) {
        return $this->stream->defaultRule();
      }

      $pattern = $this->pattern[$this->state][$index];
      $type   = $pattern[1] ?? null;
      $skip   = $pattern[2] ?? false;
      $action = $pattern[3] ?? null;

      if ($action && method_exists(jsly::class, $action)) {
        jsly::$action($type, $text, $match, $this->state, $this->context);
      }

      if ($skip) {
        return $this->next();
      }

      $stop = $this->stream->pos();
      return new Token($type, $text, $start, $stop);
    }

    return $this->stream->defaultRule();
  }
}
