<?php

namespace js4php5\compiler\lexer;

class Token
{
  /** @var string */
  private $type;

  /** @var string */
  private $text;

  /** @var Point */
  private $start;

  /** @var Point */
  private $stop;

  /**
   * @param string $type
   * @param string $text
   * @param Point  $start
   * @param Point  $stop
   */
  function __construct($type, $text, Point $start, Point $stop)
  {
    $this->type  = $type;
    $this->text  = $text;
    $this->start = $start;
    $this->stop  = $stop;
  }

  /**
   * @return Point
   */
  public function getStart()
  {
    return $this->start;
  }

  /**
   * @return Point
   */
  public function getStop()
  {
    return $this->stop;
  }

  /**
   * @return string
   */
  public function getText()
  {
    return $this->text;
  }

  /**
   * @return string
   */
  public function getType()
  {
    return $this->type;
  }

  /**
   * Return a special "null" token used to signal end-of-input.
   *
   * @return Token
   */
  public static function getNullToken()
  {
    $point = new Point(-1, -1);
    return new Token('', '', $point, $point);
  }
}
