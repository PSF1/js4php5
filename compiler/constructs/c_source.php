<?php

namespace js4php5\compiler\constructs;

class c_source extends BaseConstruct
{
  /** @var c_source|null */
  static public $that = null;

  /** @var int */
  static public $nest = 0;

  /** @var array<string,int> */
  static public $labels = [];

  /** @var BaseConstruct[] */
  public $code;

  /** @var c_var[] */
  public $vars;

  /** @var array<int, mixed> functions with function_emit() */
  public $functions;

  /** @var array<int, mixed> funcdef with toplevel_emit() */
  public $funcdef;

  /**
   * @param BaseConstruct[] $statements
   * @param array           $functions
   */
  function __construct($statements = array(), $functions = array())
  {
    $this->code = is_array($statements) ? $statements : (array) $statements;
    $this->functions = is_array($functions) ? $functions : (array) $functions;
    $this->vars = array();
    $this->funcdef = array(); // only used by toplevel object
  }

  /**
   * @param mixed $function expects object with function_emit()
   */
  static public function addFunctionExpression($function)
  {
    c_source::$that->functions[] = $function;
  }

  /**
   * @param mixed $function expects object with toplevel_emit()
   */
  static public function addFunctionDefinition($function)
  {
    if (!isset(c_program::$source) || !c_program::$source) {
      return; // Not at top-level yet
    }
    c_program::$source->funcdef[] = $function;
  }

  /**
   * @param BaseConstruct $statement
   */
  function addStatement($statement)
  {
    $this->code[] = $statement;
  }

  function addFunction($function)
  {
    $this->functions[] = $function;
  }

  function addVariable($var)
  {
    c_source::$that->vars[] = $var;
  }

  /**
   * @param bool $unusedParameter Ignored.
   *
   * @return string
   */
  function emit($unusedParameter = false)
  {
    self::$nest = 0;
    self::$labels = array();

    // Save/restore $that even if something fails
    $saved_that = c_source::$that;
    c_source::$that = $this;

    try {
      // Dump the main body
      $s = '';
      foreach ($this->code as $statement) {
        $s .= $statement->emit(true);
      }
    } finally {
      c_source::$that = $saved_that;
    }

    // Dump variable declarations collected during body traversal
    $v = c_var::really_emit($this->vars);

    // Dump function expressions (mapping)
    $f = '';
    foreach ($this->functions as $function) {
      $f .= $function->function_emit();
    }
    if ($f !== '') {
      $f = "/* function mapping */\n" . $f;
    }

    // If toplevel, dump function declarations as well
    $fd = "";
    if ($this === c_program::$source) {
      foreach ($this->funcdef as $function) {
        $fd .= $function->toplevel_emit();
      }
      if ($fd !== '') {
        $fd = "/* function declarations */\n" . $fd;
      }
      // Wrap into run method; JS::init() is available via compiled use statements
      return "    static public function run(){\n            JS::init();\n        " . $f . $v . $s . "\n}\n\n" . $fd;
    }

    // Non-toplevel: return mapping + vars + statements
    return $fd . $f . $v . $s;
  }
}
