<?php

namespace js4php5\compiler\parser;

use js4php5\compiler\lexer\Lexer;
use js4php5\compiler\lexer\Token;

class DefaultParserStrategy extends ParserStrategy
{
  /**
   * @param Token $token
   * @param Lexer $lex
   *
   * @throws ParseException
   */
  public function assertDone(Token $token, Lexer $lex)
  {
    if ($token->getType()) {
      $this->stuck($token, $lex, []);
    }
  }

  /**
   * @param Token $token
   * @param Lexer $lex
   * @param mixed $stack
   *
   * @throws ParseException
   */
  public function stuck(Token $token, Lexer $lex, $stack)
  {
    throw new ParseException(
      "Parser stuck; source and grammar do not agree. Can't tell what to do with token.",
      $token,
      $token->getStart()
    );

    /*
    // Legacy debug output (removed to avoid accidental HTML output in library code)
    // Helpers::send_parse_error_css_styles();
    // echo "<hr/>The LR parser is stuck. Source and grammar do not agree.<br/>";
    // Helpers::span('term', $token->text, $token->type);
    // $lex->report_error();
    // while (count($stack)) {
    //     $tos = array_pop($stack);
    //     echo $tos->trace() . "<br/>\n";
    // }
    // throw new parse_error("Can't tell what to do with " . $token->type . ".");
    */
  }
}
