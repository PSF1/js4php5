<?php

namespace js4php5\compiler\constructs;

use js4php5\compiler\Compiler;

class c_try extends BaseConstruct
{
  /** @var BaseConstruct|string */
  public $body;

  /** @var BaseConstruct|string|null */
  public $catch;

  /** @var BaseConstruct|string|null */
  public $final;

  /** @var string */
  public $id_try;

  /** @var string */
  public $id_catch;

  /** @var string */
  public $id_finally;

  /**
   * @param BaseConstruct $code
   * @param BaseConstruct|null $catch
   * @param BaseConstruct|null $final
   */
  function __construct($code, $catch = null, $final = null)
  {
    $this->body   = $code;
    $this->catch  = $catch;
    $this->final  = $final;
    $this->id_try     = Compiler::generateSymbol("jsrt_try");
    $this->id_catch   = Compiler::generateSymbol("jsrt_catch");
    $this->id_finally = Compiler::generateSymbol("jsrt_finally");
  }

  /**
   * Define helper functions for try/catch/finally at top-level.
   * toplevel_emit is called after emit() has converted body/catch/final to strings.
   */
  function toplevel_emit()
  {
    $o  = "function " . $this->id_try . "() {\n";
    $o .= "  try ";
    $o .= trim(str_replace("\n", "\n  ", $this->body));
    // Use fully-qualified Exception to work in any namespace
    $o .= " catch (\\Exception \$e) {\n";
    $o .= "    Runtime::\$exception = \$e;\n";
    $o .= "  }\n";
    $o .= "  return NULL;\n";
    $o .= "}\n";

    if ($this->catch != null) {
      $o .= "function " . $this->id_catch . "() {\n";
      $o .= "  " . trim(str_replace("\n", "\n  ", $this->catch));
      $o .= "\n  return NULL;\n";
      $o .= "}\n";
    }
    if ($this->final != null) {
      $o .= "function " . $this->id_finally . "() {\n";
      $o .= "  " . trim(str_replace("\n", "\n  ", $this->final));
      $o .= "\n  return NULL;\n";
      $o .= "}\n";
    }
    return $o;
  }

  /**
   * @param bool $unusedParameter
   *
   * @return string
   */
  function emit($unusedParameter = false)
  {
    // Put catch() and finally blocks in functions to control when to evaluate them
    c_source::addFunctionDefinition($this);

    $id = ($this->catch != null) ? $this->catch->id : '';

    // Convert blocks to strings now for toplevel_emit
    $this->body = $this->body->emit(true);
    if ($this->catch != null) {
      $this->catch = $this->catch->emit(true);
    }
    if ($this->final != null) {
      $this->final = $this->final->emit(true);
    }

    $ret = Compiler::generateSymbol("jsrt_ret");
    $tmp = Compiler::generateSymbol("jsrt_tmp");

    // try is on its own to work around old PHP crashes with exceptions in call_user_func
    $o  = "\$$tmp = " . $this->id_try . "();\n";
    $o .= "\$$ret = Runtime::trycatch(\$$tmp, ";
    $o .= ($this->catch != null ? "'" . $this->id_catch . "'" : "NULL") . ", ";
    $o .= ($this->final != null ? "'" . $this->id_finally . "'" : "NULL");
    $o .= ($id !== '' ? ", '" . $id . "'" : "") . ");\n";
    $o .= "if (\$$ret != NULL) return \$$ret;\n";
    return $o;
  }
}
