<?php

namespace js4php5\compiler\constructs;

class c_var extends BaseConstruct
{
  /** @var array<int, array{0:string,1:mixed}> */
  public $vars;

  /**
   * @param array<int, array{0:string,1:mixed}> $args Array where each value contains [[0] => 'var_name', [1] => BaseConstruct|mixed]
   */
  function __construct(array $args)
  {
    //TODO: Find out why 'var foobar;' (declare but don't initialize) gets value null instead of c_literal_null.
    // Maybe need to add a js_undefined object and update parse rules?
    //TODO: After fixed (if needs to be), remove hack in BaseBinaryConstruct::emit().
    $this->vars = $args;
  }

  static public function really_emit($arr)
  {
    if (count($arr) == 0) {
      return '';
    }
    $l = "'" . implode("','", array_unique($arr)) . "'";
    return "Runtime::define_variables($l);\n";
  }

  function emit_for()
  {
    // Ensure side-effects (variable registration/initializers) are emitted
    $this->emit(true);
    return "Runtime::id('" . $this->vars[0][0] . "')";
  }

  /**
   * @param bool $unusedParameter
   *
   * @return string PHP code chunk
   */
  function emit($unusedParameter = false)
  {
    $o = '';
    foreach ($this->vars as $var) {
      /**
       * @var string        $id
       * @var BaseConstruct $init
       */
      if (!is_array($var) || count($var) < 1) {
        continue;
      }
      $id = (string)$var[0];
      $init = $var[1] ?? null;

      // Register variable name in current source
      c_source::$that->addVariable($id);

      // Emit initializer only if it is a construct with emit()
      if (is_object($init) && method_exists($init, 'emit')) {
        $obj = new c_assign(new c_identifier($id), $init);
        $o  .= $obj->emit(true) . ";\n";
      }
    }
    return $o;
  }
}
