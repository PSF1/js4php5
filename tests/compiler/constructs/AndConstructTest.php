<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_and;

/**
 * Stub de argumento que expone emit() y marca si se pidió getValue.
 */
class AndArgStub extends BaseConstruct
{
  private string $token;

  public function __construct(string $token)
  {
    $this->token = $token;
  }

  // Coincide con la firma del padre (sin return type)
  public function emit($getValue = false)
  {
    // Añade sufijo para probar la propagación del flag
    return $this->token . ($getValue ? '_gv' : '');
  }
}

final class AndConstructTest extends TestCase
{
  public function testEmitGeneratesShortCircuitPatternWithTempSymbol(): void
  {
    $left  = new AndArgStub('LEFT');
    $right = new AndArgStub('RIGHT');

    $node = new c_and($left, $right);
    $php  = $node->emit();

    // Patrón: (!Runtime::js_bool($scN=LEFT_gv)?$scN:RIGHT_gv)
    $this->assertMatchesRegularExpression(
      '/^\(!Runtime::js_bool\(\$(sc\d+)=LEFT_gv\)\?\$\1:RIGHT_gv\)$/',
      $php
    );
  }

  public function testEmitAlwaysAsksValueForBothSides(): void
  {
    $left  = new AndArgStub('LHS');
    $right = new AndArgStub('RHS');

    $node = new c_and($left, $right);
    $php  = $node->emit();

    $this->assertStringContainsString('LHS_gv', $php);
    $this->assertStringContainsString('RHS_gv', $php);
  }
}
