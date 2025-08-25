<?php

namespace js4php5\compiler\constructs;

class c_catch extends BaseConstruct
{
  /** @var string */
  public $id;

  /** @var BaseConstruct */
  public $code;

  /**
   * @param string       $id
   * @param BaseConstruct $code
   */
  function __construct($id, $code)
  {
    // Normalize identifier to string
    $this->id = (string) $id;

    // Ensure $code is a construct we can emit; wrap arrays/null into a c_block
    if ($code instanceof BaseConstruct) {
      $this->code = $code;
    } else {
      // Accept array of statements or single value and wrap
      $this->code = new c_block(is_array($code) ? $code : (array) $code);
    }
  }

  function emit($unusedParameter = false)
  {
    // This node simply delegates to the contained block/construct.
    // Variable binding for "catch (id)" is handled by surrounding compiler logic.
    return $this->code->emit(true);
  }
}
