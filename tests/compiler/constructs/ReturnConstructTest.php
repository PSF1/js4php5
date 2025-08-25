<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_return;

class ReturnExprStub extends BaseConstruct
{
  private string $token;
  public function __construct(string $token) { $this->token = $token; }
  public function emit($getValue = false)
  {
    // Return a token with _gv when value is requested
    return $this->token . ($getValue ? '_gv' : '');
  }
}

final class ReturnConstructTest extends TestCase
{
  public function testReturnWithExpressionEmitsValue(): void
  {
    $expr = new ReturnExprStub('EXPR');
    $node = new c_return($expr);

    $php = $node->emit();

    $this->assertSame("return EXPR_gv;\n", $php);
  }

  public function testReturnWithoutExpressionEmitsRuntimeUndefined(): void
  {
    // Semicolon sentinel means "return;" (sin expresión)
    $node = new c_return(';');

    $php = $node->emit();

    $this->assertSame("return Runtime::\$undefined;\n", $php);
  }

  public function testReturnWithNullTreatsAsNoExpression(): void
  {
    // Null se trata como "return;" (sin expresión)
    $node = new c_return(null);

    $php = $node->emit();

    $this->assertSame("return Runtime::\$undefined;\n", $php);
  }
}
