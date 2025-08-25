<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_label;
use js4php5\compiler\constructs\c_source;

/**
 * Stub para bloques que muestra si se llama con getValue=true.
 */
class LabelBlockStub extends BaseConstruct
{
  public function emit($getValue = false)
  {
    return $getValue ? "BLOCK_gv\n" : "BLOCK\n";
  }
}

final class LabelConstructTest extends TestCase
{
  protected function setUp(): void
  {
    // Reset del estado de compilación
    c_source::$nest = 0;
    c_source::$labels = [];
  }

  public function testEmitRegistersLabelAtCurrentNestAndDelegatesToBlock(): void
  {
    c_source::$nest = 3;
    $block = new LabelBlockStub();

    $node = new c_label('myLabel', $block);
    $out  = $node->emit();

    // La etiqueta debe quedar registrada en el nivel de anidamiento actual
    $this->assertArrayHasKey('myLabel', c_source::$labels);
    $this->assertSame(3, c_source::$labels['myLabel']);

    // Debe delegar a emit(true) del bloque
    $this->assertSame("BLOCK_gv\n", $out);
  }

  public function testConstructorStripsColonSuffixFromLabel(): void
  {
    c_source::$nest = 1;
    $block = new LabelBlockStub();

    $node = new c_label('myLabel:extra', $block);
    $node->emit();

    // El nombre efectivo de la etiqueta debe ser la parte antes de ':'
    $this->assertArrayHasKey('myLabel', c_source::$labels);
    $this->assertSame(1, c_source::$labels['myLabel']);
    $this->assertArrayNotHasKey('myLabel:extra', c_source::$labels);
  }
}
