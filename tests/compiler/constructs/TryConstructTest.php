<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_try;

/**
 * Bloque que simula un c_block: devuelve un bloque con llaves y salto de línea.
 */
class TryBlockStub extends BaseConstruct
{
  private string $code;
  public function __construct(string $code) { $this->code = $code; }
  public function emit($getValue = false)
  {
    // Simula formato típico de c_block
    return "{\n  " . str_replace("\n", "\n  ", trim($this->code)) . "\n}\n";
  }
}

/**
 * Simula c_catch: requiere un id y un bloque con emit(true).
 */
class TryCatchStub extends BaseConstruct
{
  public string $id;
  private BaseConstruct $block;
  public function __construct(string $id, BaseConstruct $block)
  {
    $this->id = $id;
    $this->block = $block;
  }
  public function emit($getValue = false)
  {
    // Devuelve el bloque ya compilado (como hace c_catch)
    return $this->block->emit(true);
  }
}

final class TryConstructTest extends TestCase
{
  public function testEmitGeneratesTrycatchCallWithIds(): void
  {
    // Arrange
    $body    = new TryBlockStub("doTry();");
    $catch   = new TryCatchStub('e', new TryBlockStub("doCatch();"));
    $finally = new TryBlockStub("doFin();");

    $node = new c_try($body, $catch, $finally);

    // Act
    $php = $node->emit();

    // Assert
    // 1) The temporary assignment to the try function result: $jsrt_tmpN = jsrt_tryN();
    $this->assertMatchesRegularExpression('/^\$jsrt_tmp\d+\s=\sjsrt_try\d+\(\);/m', $php);

    // 2) The Runtime::trycatch call with the generated IDs and catch identifier 'e'
    // Use single-quoted regex to avoid PHP variable interpolation of $jsrt_tmp
    $this->assertMatchesRegularExpression(
      '/Runtime::trycatch\(\$jsrt_tmp\d+, \'jsrt_catch\d+\', \'jsrt_finally\d+\', \'e\'\);/',
      $php
    );

    // 3) If a value is returned from trycatch, it must be returned by the compiled code
    $this->assertMatchesRegularExpression('/if \(\$jsrt_ret\d+ != NULL\) return \$jsrt_ret\d+;/', $php);
  }

  public function testToplevelEmitDefinesThreeFunctionsWithQualifiedException(): void
  {
    $body   = new TryBlockStub("t();");
    $catch  = new TryCatchStub('err', new TryBlockStub("c();"));
    $finally= new TryBlockStub("f();");

    $node = new c_try($body, $catch, $finally);

    // Primero compilar para que las propiedades body/catch/final se transformen en strings
    $node->emit();

    $out = $node->toplevel_emit();

    // Función try
    $this->assertMatchesRegularExpression('/^function jsrt_try\d+\(\) \{\s*^  try \{\s*^    t\(\);/m', $out);

    // Debe usar catch (\Exception $e) y asignar Runtime::$exception
    $this->assertStringContainsString('catch (\Exception $e) {', $out);
    $this->assertStringContainsString('Runtime::$exception = $e;', $out);

    // Función catch presente
    $this->assertMatchesRegularExpression('/function jsrt_catch\d+\(\) \{\s*^  \{\s*^    c\(\);/m', $out);

    // Función finally presente
    $this->assertMatchesRegularExpression('/function jsrt_finally\d+\(\) \{\s*^  \{\s*^    f\(\);/m', $out);

    // Cada función termina con "return NULL;"
    $this->assertEquals(3, substr_count($out, 'return NULL;'));
  }

  public function testOnlyCatchNoFinally(): void
  {
    $node = new c_try(new TryBlockStub('t();'), new TryCatchStub('e', new TryBlockStub('c();')), null);
    $node->emit();
    $out = $node->toplevel_emit();

    $this->assertStringContainsString('function jsrt_try', $out);
    $this->assertStringContainsString('function jsrt_catch', $out);
    $this->assertStringNotContainsString('function jsrt_finally', $out);
  }

  public function testOnlyFinallyNoCatch(): void
  {
    $node = new c_try(new TryBlockStub('t();'), null, new TryBlockStub('f();'));
    $node->emit();
    $out = $node->toplevel_emit();

    $this->assertStringContainsString('function jsrt_try', $out);
    $this->assertStringNotContainsString('function jsrt_catch', $out);
    $this->assertStringContainsString('function jsrt_finally', $out);
  }
}
