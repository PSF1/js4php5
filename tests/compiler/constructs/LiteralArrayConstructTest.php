<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_literal_array;
use js4php5\compiler\constructs\c_literal_null;

class LiteralArrayEmitStub extends BaseConstruct
{
  private string $token;
  public function __construct(string $token) { $this->token = $token; }
  public function emit($getValue = false)
  {
    // Always emit value; no suffix necesario
    return $this->token;
  }
}

final class LiteralArrayConstructTest extends TestCase
{
  public function testEmitWithTwoElements(): void
  {
    $e0 = new LiteralArrayEmitStub('E0');
    $e1 = new LiteralArrayEmitStub('E1');

    $node = new c_literal_array([$e0, $e1]);
    $php  = $node->emit();

    $this->assertSame('Runtime::literal_array(E0,E1)', $php);
  }

  public function testEmitSkipsNullEntries(): void
  {
    $e0 = new LiteralArrayEmitStub('A');
    $e2 = new LiteralArrayEmitStub('C');

    $node = new c_literal_array([$e0, null, $e2]);
    $php  = $node->emit();

    $this->assertSame('Runtime::literal_array(A,C)', $php);
  }

  public function testSingleLiteralNullProducesEmptyArray(): void
  {
    $nullNode = new c_literal_null();
    $node     = new c_literal_array([$nullNode]);
    $php      = $node->emit();

    $this->assertSame('Runtime::literal_array()', $php);
  }

  public function testConstructorAcceptsNonArrayAndWraps(): void
  {
    $single = new LiteralArrayEmitStub('X');
    // Pass a single node; constructor must wrap it
    $node = new c_literal_array($single);
    $php  = $node->emit();

    $this->assertSame('Runtime::literal_array(X)', $php);
  }
}
