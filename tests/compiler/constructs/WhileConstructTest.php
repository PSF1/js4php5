<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_while;
use js4php5\compiler\constructs\c_source;

/**
 * Condición: devuelve un token y marca getValue con sufijo _gv.
 */
class WhileCondStub extends BaseConstruct
{
  private string $token;
  public function __construct(string $token = 'COND') { $this->token = $token; }
  public function emit($getValue = false)
  {
    return $this->token . ($getValue ? '_gv' : '');
  }
}

/**
 * Cuerpo: simula un bloque con llaves y salto de línea final.
 */
class WhileBodyStub extends BaseConstruct
{
  private string $code;
  public function __construct(string $code = 'doIt();') { $this->code = $code; }
  public function emit($getValue = false)
  {
    return "{\n  " . str_replace("\n", "\n  ", trim($this->code)) . "\n}\n";
  }
}

/**
 * Cuerpo que lanza excepción en emit() para probar restauración de anidamiento.
 */
class WhileBodyThrowingStub extends BaseConstruct
{
  public function emit($getValue = false)
  {
    throw new \RuntimeException('emit failed');
  }
}

final class WhileConstructTest extends TestCase
{
  protected function setUp(): void
  {
    c_source::$nest = 0;
    c_source::$labels = [];
  }

  public function testEmitBuildsWhileWithJsBoolAndBodyAndAppendsNewline(): void
  {
    $cond = new WhileCondStub('C');
    $body = new WhileBodyStub("a();\nb();");

    $node = new c_while($cond, $body);
    $out  = $node->emit();

    $this->assertSame("while (Runtime::js_bool(C_gv)) " . $body->emit(true) . "\n", $out);
  }

  public function testEmitRestoresNestEvenOnSuccess(): void
  {
    $start = 3;
    c_source::$nest = $start;

    $node = new c_while(new WhileCondStub(), new WhileBodyStub());
    $node->emit();

    $this->assertSame($start, c_source::$nest);
  }

  public function testEmitRestoresNestEvenOnExceptionInBody(): void
  {
    $start = 5;
    c_source::$nest = $start;

    $node = new c_while(new WhileCondStub(), new WhileBodyThrowingStub());

    try {
      $node->emit();
      $this->fail('Expected RuntimeException was not thrown');
    } catch (\RuntimeException $e) {
      $this->assertSame('emit failed', $e->getMessage());
    }

    // Debe restaurar el valor original aunque haya fallo en el cuerpo
    $this->assertSame($start, c_source::$nest);
  }
}
