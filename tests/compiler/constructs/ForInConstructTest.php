<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_for_in;
use js4php5\compiler\constructs\c_source;

/**
 * Stub for the "list" (right-hand) expression; marks getValue=true via suffix.
 */
class ForInListStub extends BaseConstruct
{
  private string $token;
  public function __construct(string $token) { $this->token = $token; }
  public function emit($getValue = false)
  {
    return $this->token . ($getValue ? '_gv' : '');
  }
}

/**
 * Stub for the "one" (left-hand) target; here we test the non-c_var path.
 */
class ForInTargetStub extends BaseConstruct
{
  private string $code;
  public function __construct(string $code) { $this->code = $code; }
  public function emit($getValue = false)
  {
    // LHS for assignment should be emitted as reference (no _gv expected)
    return $this->code;
  }
}

/**
 * Stub for the body statement to validate indentation.
 */
class ForInBodyStub extends BaseConstruct
{
  private string $code;
  public function __construct(string $code) { $this->code = $code; }
  public function emit($getValue = false)
  {
    return $this->code;
  }
}

final class ForInConstructTest extends TestCase
{
  protected function setUp(): void
  {
    // Reset nesting
    c_source::$nest = 0;
    c_source::$labels = [];
  }

  public function testEmitBuildsForeachAssignsKeyAsStringAndIndentsBody(): void
  {
    $list = new ForInListStub('LIST');
    $one  = new ForInTargetStub('TARGET');
    $body = new ForInBodyStub("body1();\nbody2();");

    $node = new c_for_in($one, $list, $body);
    $code = $node->emit();

    // foreach (LIST_gv as $fvN) {
    $this->assertMatchesRegularExpression('/^foreach \(LIST_gv as \$fv\d+\) \{/', $code);

    // Extract generated symbol to assert the assignment line
    $this->assertMatchesRegularExpression('/as \$(fv\d+)\) \{/', $code, 'No foreach symbol found');
    preg_match('/as \$(fv\d+)\) \{/', $code, $m);
    $sym = $m[1];

    // Expect assignment line using the key coerced to string
    $this->assertStringContainsString("Runtime::expr_assign(TARGET, Runtime::js_str(\$$sym));", $code);

    // Body should be indented by two spaces per line
    $this->assertStringContainsString("  body1();\n  body2();", $code);

    // Closing brace present
    $this->assertStringContainsString("}\n", $code);
  }

  public function testNestCounterIsRestoredAfterEmit(): void
  {
    $startNest = 5;
    c_source::$nest = $startNest;

    $node = new c_for_in(
      new ForInTargetStub('LHS'),
      new ForInListStub('R'),
      new ForInBodyStub('noop();')
    );
    $node->emit();

    $this->assertSame($startNest, c_source::$nest);
  }
}
