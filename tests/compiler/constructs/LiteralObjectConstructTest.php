<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_literal_object;

class LiteralObjectPairStub extends BaseConstruct
{
  private string $emitted;
  public function __construct(string $emitted) { $this->emitted = $emitted; }
  public function emit($getValue = false)
  {
    // Return the prebuilt "key,value" segment
    return $this->emitted;
  }
}

final class LiteralObjectConstructTest extends TestCase
{
  public function testEmitEmptyObject(): void
  {
    $node = new c_literal_object([]);
    $php  = $node->emit();
    $this->assertSame('Runtime::literal_object()', $php);
  }

  public function testEmitSinglePair(): void
  {
    // Pair stub emits "K1,V1" (e.g., "Runtime::js_str('a'),EXPR")
    $pair = new LiteralObjectPairStub("K1,V1");

    $node = new c_literal_object([$pair]);
    $php  = $node->emit();

    $this->assertSame('Runtime::literal_object(K1,V1)', $php);
  }

  public function testEmitMultiplePairs(): void
  {
    $p1 = new LiteralObjectPairStub("K1,V1");
    $p2 = new LiteralObjectPairStub("K2,V2");

    $node = new c_literal_object([$p1, $p2]);
    $php  = $node->emit();

    $this->assertSame('Runtime::literal_object(K1,V1,K2,V2)', $php);
  }

  public function testConstructorAcceptsSingleNode(): void
  {
    $pair = new LiteralObjectPairStub("KA,VA");

    // Pass a single node (not an array); constructor should wrap it
    $node = new c_literal_object($pair);
    $php  = $node->emit();

    $this->assertSame('Runtime::literal_object(KA,VA)', $php);
  }

  public function testEmitSkipsNullEntriesSafely(): void
  {
    $p1 = new LiteralObjectPairStub("K1,V1");
    // Include a null hole (sparse object in AST)
    $node = new c_literal_object([$p1, null]);
    $php  = $node->emit();

    $this->assertSame('Runtime::literal_object(K1,V1)', $php);
  }
}
