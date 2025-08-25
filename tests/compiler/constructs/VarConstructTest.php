<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_var;
use js4php5\compiler\constructs\c_source;

/**
 * RHS stub that returns a token and appends _gv when a value is requested.
 */
class VarInitStub extends BaseConstruct
{
  private string $token;
  public function __construct(string $token) { $this->token = $token; }
  public function emit($getValue = false)
  {
    return $this->token . ($getValue ? '_gv' : '');
  }
}

final class VarConstructTest extends TestCase
{
  protected function setUp(): void
  {
    // Reset c_source::$that to a fresh source to collect vars
    c_source::$that = new c_source([], []);
  }

  public function testReallyEmitEmptyReturnsEmptyString(): void
  {
    $this->assertSame('', c_var::really_emit([]));
  }

  public function testReallyEmitDefinesUniqueVariables(): void
  {
    $out = c_var::really_emit(['a', 'b', 'a']);
    // Order is not critical beyond uniqueness, but the function uses array_unique then implode
    $this->assertSame("Runtime::define_variables('a','b');\n", $out);
  }

  public function testEmitGeneratesAssignmentsOnlyForInitializedEntries(): void
  {
    // Three declarations: a with init, b without init, c with a non-object "init" (should be ignored)
    $aInit = new VarInitStub('INIT_A');
    $vars = [
      ['a', $aInit],
      ['b', null],
      ['c', 'NOT_AN_OBJECT'],
    ];

    $node = new c_var($vars);
    $php  = $node->emit();

    // Should emit assignment only for 'a'
    $this->assertSame("Runtime::expr_assign(Runtime::id('a'),INIT_A_gv);\n", $php);

    // All declared identifiers must be registered in c_source::$that->vars
    $this->assertContains('a', c_source::$that->vars);
    $this->assertContains('b', c_source::$that->vars);
    $this->assertContains('c', c_source::$that->vars);
  }

  public function testEmitForEmitsReferenceToFirstVarAndTriggersEmitSideEffects(): void
  {
    $init = new VarInitStub('I');
    $node = new c_var([['x', $init]]);
    $ref  = $node->emit_for();

    // emit_for returns a Runtime::id('name') reference
    $this->assertSame("Runtime::id('x')", $ref);
  }
}
