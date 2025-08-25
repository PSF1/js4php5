<?php

namespace js4php5\compiler\constructs;

use js4php5\compiler\Compiler;

/**
 * Function definition construct.
 *
 * Accepts: [id, params[], bodyNode]
 * - id: string function name in JS source
 * - params: array of parameter names (strings)
 * - body: a node with emit() that produces the function body
 */
class c_function_definition extends BaseConstruct
{
  /** @var string JS function name */
  public $id;

  /** @var string[] Parameter names */
  public $params = [];

  /** @var BaseConstruct Body node */
  public $body;

  /** @var string Generated PHP function identifier */
  public $phpid;

  /**
   * @param array{id:string,params:array,body:BaseConstruct}|array $args [id, params[], bodyNode]
   */
  function __construct($args)
  {
    // Expect [$id, $params, $body]
    $this->id     = (string) ($args[0] ?? '');
    $this->params = array_values(is_array($args[1] ?? []) ? ($args[1] ?? []) : []);
    $this->body   = $args[2] ?? null;

    // Generate a unique id for the function (used by Runtime)
    $this->phpid = Compiler::generateSymbol('uf');

    // Inform source collector (if needed by the compiler pipeline)
    if (method_exists(c_source::class, 'addFunctionDefinition')) {
      c_source::addFunctionDefinition($this);
    }
  }

  /**
   * Pre-emit the function definition and return the define_function call.
   * The actual function body is expected to be injected by the compilation pipeline.
   */
  public function function_emit(): string
  {
    // Prepare parameter list
    $quoted = array_map(static function ($p) {
      return "'" . (string)$p . "'";
    }, $this->params);

    // Return a Runtime::define_function('<phpid>','<name>', array('p1','p2'));
    return "Runtime::define_function('{$this->phpid}','{$this->id}',array(" . implode(',', $quoted) . "));\n";
  }

  /**
   * Emit the expression-time handle to this function: a Runtime function id.
   */
  function emit($getValue = false)
  {
    return "Runtime::function_id('{$this->phpid}')";
  }
}
