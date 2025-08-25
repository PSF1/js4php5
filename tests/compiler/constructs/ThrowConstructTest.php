<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_throw;

/**
 * Expresión stub que devuelve un token y marca getValue con sufijo _gv.
 */
class ThrowExprStub extends BaseConstruct
{
  private string $token;
  public function __construct(string $token) { $this->token = $token; }
  public function emit($getValue = false)
  {
    return $this->token . ($getValue ? '_gv' : '');
  }
}

final class ThrowConstructTest extends TestCase
{
  public function testEmitBuildsThrowWithFqcnJsExceptionAndExprValue(): void
  {
    $expr = new ThrowExprStub('EXPR');
    $node = new c_throw($expr);

    $php = $node->emit();

    // Debe lanzar la excepción con FQCN para que funcione en el namespace del script compilado
    $this->assertSame("throw new \\js4php5\\runtime\\jsException(EXPR_gv);\n", $php);
  }
}
