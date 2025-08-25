<?php

namespace js4php5\compiler\parser;

class distinguishing_table
{
  /** @var array<string,bool> */
  private $dist;

  /**
   * Modern constructor; inicializa la tabla interna.
   */
  public function __construct()
  {
    $this->dist = [];
  }

  /**
   * Compatibilidad retro PHP4-style: invoca __construct().
   */
  public function distinguishing_table()
  {
    $this->__construct();
  }

  /**
   * Construye la clave canónica del par, independiente del orden.
   *
   * @param string $s1
   * @param string $s2
   * @return string
   */
  public function key($s1, $s2)
  {
    $them = [$s1, $s2];
    sort($them);
    return implode('|', $them);
  }

  /**
   * Marca el par (s1,s2) como distinguible.
   *
   * @param string $s1
   * @param string $s2
   * @return void
   */
  public function distinguish($s1, $s2)
  {
    $key = $this->key($s1, $s2);
    $this->dist[$key] = true;
  }

  /**
   * Indica si el par (s1,s2) está marcado como distinguible (orden indiferente).
   *
   * @param string $s1
   * @param string $s2
   * @return bool
   */
  public function differ($s1, $s2)
  {
    $key = $this->key($s1, $s2);
    return isset($this->dist[$key]);
  }
}
