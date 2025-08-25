<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\c_function_definition;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_source;

/**
 * Simple body stub that returns a multi-line body for the function.
 */
class FnBodyStub extends BaseConstruct
{
  private string $code;
  public function __construct(string $code) { $this->code = $code; }
  public function emit($getValue = false)
  {
    // Simulate body emission (already indented by the function generator)
    return $this->code;
  }
}

final class FunctionDefinitionConstructTest extends TestCase
{
  protected function setUp(): void
  {
    // Reset any global tracking on c_source if exists
    if (property_exists(c_source::class, 'lastDef')) {
      c_source::$lastDef = null;
    }
  }

  public function testFunctionEmitReturnsDefineCallAndEmitReturnsFunctionId(): void
  {
    $id     = 'myFunc';
    $params = ['a', 'b'];
    $body   = new FnBodyStub("    // body\n    echo \"hi\";\n");

    $node = new c_function_definition([$id, $params, $body]);

    // function_emit should return a define_function() call with a generated phpid
    $define = $node->function_emit();

    $this->assertMatchesRegularExpression(
      "/^Runtime::define_function\\('([A-Za-z0-9_]+)','myFunc',array\\('a','b'\\)\\);\\n$/",
      $define,
      'function_emit must define the function with a generated phpid and the given name/params'
    );

    // Capture the phpid from the returned define call
    preg_match("/^Runtime::define_function\\('([A-Za-z0-9_]+)'/", $define, $m);
    $phpidFromDefine = $m[1] ?? null;

    // emit(true) should resolve to Runtime::function_id('<phpid>')
    $expr = $node->emit(true);
    $this->assertSame("Runtime::function_id('{$phpidFromDefine}')", $expr);

    // If c_source tracks last definition, ensure it got set to this node
    if (property_exists(c_source::class, 'lastDef')) {
      $this->assertSame($node, c_source::$lastDef);
    }
  }
}
