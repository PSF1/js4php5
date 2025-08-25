<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_print;

/**
 * Stub de argumento que marca si se pide valor appending '_gv'.
 */
class PrintArgStub extends BaseConstruct
{
  private string $token;
  public function __construct(string $token) { $this->token = $token; }
  public function emit($getValue = false)
  {
    return $this->token . ($getValue ? '_gv' : '');
  }
}

final class PrintConstructTest extends TestCase
{
  public function testEmitWithOnlyConstructArgs(): void
  {
    $a = new PrintArgStub('A');
    $b = new PrintArgStub('B');

    $node = new c_print($a, $b);
    $php  = $node->emit();

    $this->assertSame("Runtime::write( (A_gv),(B_gv) );\n", $php);
  }

  public function testEmitWithMixedArgsConstructAndRaw(): void
  {
    $a = new PrintArgStub('X');
    $raw = "'RAW'"; // token literal ya compilado

    $node = new c_print($a, $raw);
    $php  = $node->emit();

    $this->assertSame("Runtime::write( (X_gv),('RAW') );\n", $php);
  }

  public function testEmitWithNoArgsProducesEmptyCall(): void
  {
    $node = new c_print();
    $php  = $node->emit();

    $this->assertSame("Runtime::write( );\n", $php);
  }
}
