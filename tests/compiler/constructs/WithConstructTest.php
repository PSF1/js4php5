<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_with;

/**
 * Stub de expresión: devuelve un token y marca getValue con sufijo _gv.
 */
class WithExprStub extends BaseConstruct
{
  private string $token;
  public function __construct(string $token = 'EXPR') { $this->token = $token; }
  public function emit($getValue = false)
  {
    return $this->token . ($getValue ? '_gv' : '');
  }
}

/**
 * Stub de cuerpo: simula un bloque con llaves y salto de línea final.
 */
class WithBodyStub extends BaseConstruct
{
  private string $code;
  public function __construct(string $code = 'doIt();') { $this->code = $code; }
  public function emit($getValue = false)
  {
    return "{\n  " . str_replace("\n", "\n  ", trim($this->code)) . "\n}\n";
  }
}

final class WithConstructTest extends TestCase
{
  public function testEmitPushesScopeWithRuntimeJsObjEmitsBodyAndPopsScope(): void
  {
    $expr = new WithExprStub('OBJ');
    $body = new WithBodyStub("a();\nb();");

    $node = new c_with($expr, $body);
    $out  = $node->emit();

    // Debe empujar el scope con Runtime::js_obj(OBJ_gv)
    $this->assertStringContainsString("Runtime::push_scope(Runtime::js_obj(OBJ_gv));\n", $out);

    // Debe contener el cuerpo tal cual
    $this->assertStringContainsString("{\n  a();\n  b();\n}\n", $out);

    // Debe hacer pop_scope al final
    $this->assertStringContainsString("Runtime::pop_scope();\n", $out);

    // Orden: push -> body -> pop
    $pushPos = strpos($out, "Runtime::push_scope");
    $bodyPos = strpos($out, "{\n  a();");
    $popPos  = strpos($out, "Runtime::pop_scope();");
    $this->assertNotFalse($pushPos);
    $this->assertNotFalse($bodyPos);
    $this->assertNotFalse($popPos);
    $this->assertTrue($pushPos < $bodyPos && $bodyPos < $popPos);
  }
}
