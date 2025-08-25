<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\c_program;
use js4php5\compiler\constructs\c_source;

/**
 * Stub de c_source para observar el parámetro getValue y devolver un código fijo.
 */
class ProgramSourceStub extends c_source
{
  /** @var bool|null */
  public ?bool $lastGetValue = null;

  public function emit($getValue = false)
  {
    $this->lastGetValue = (bool)$getValue;
    return "SRC_CODE";
  }
}

final class ProgramConstructTest extends TestCase
{
  protected function setUp(): void
  {
    // No necesitamos resetear estado estático para estas aserciones.
  }

  public function testConstructorStoresSourceAndStaticReference(): void
  {
    $src = new ProgramSourceStub();
    $node = new c_program($src);

    // La propiedad estática debe apuntar al mismo objeto
    $this->assertSame($src, c_program::$source);
  }

  public function testEmitDelegatesToSourceWithGetValueTrueAndReturnsItsCode(): void
  {
    $src = new ProgramSourceStub();
    $node = new c_program($src);

    $out = $node->emit();

    $this->assertSame('SRC_CODE', $out);
    $this->assertTrue($src->lastGetValue, 'emit() debe llamar a $src->emit(true)');
  }
}
