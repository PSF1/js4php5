<?php

namespace js4php5\compiler\parser;

use Exception;
use Throwable;
use js4php5\compiler\lexer\Point;
use js4php5\compiler\lexer\Token;

class ParseException extends Exception
{
  /** @var Token */
  private $token;

  /** @var Point */
  private $pos;

  /**
   * @param string         $message
   * @param Token          $token
   * @param Point          $pos
   * @param int            $code
   * @param Throwable|null $previous
   */
  public function __construct(string $message, Token $token, Point $pos, int $code = 0, ?Throwable $previous = null)
  {
    parent::__construct($message, $code, $previous);
    $this->token = $token;
    $this->pos   = $pos;
  }

  /**
   * @return Point
   */
  public function getPos()
  {
    return $this->pos;
  }

  /**
   * @return Token
   */
  public function getToken()
  {
    return $this->token;
  }
}
