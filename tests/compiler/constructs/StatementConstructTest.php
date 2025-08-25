<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_statement;

/**
 * Stub that shows whether getValue was requested by appending '_gv'.
 */
class StatementChildStub extends BaseConstruct
{
  private string $token;
  public function __construct(string $token) { $this->token = $token; }

  // Match parent's signature (no return type)
  public function emit($getValue = false)
  {
    return $this->token . ($getValue ? '_gv' : '');
  }
}

final class StatementConstructTest extends TestCase
{
  public function testEmitDelegatesToChildWithGetValueAndAppendsSemicolonNewline(): void
  {
    $child = new StatementChildStub('EXPR');
    $stmt  = new c_statement($child);

    $out = $stmt->emit();

    $this->assertSame("EXPR_gv;\n", $out);
  }

  public function testEmitAlwaysAppendsOnlyOneSemicolonNewline(): void
  {
    $child = new class('CODE') extends BaseConstruct {
      private string $token;
      public function __construct(string $token) { $this->token = $token; }
      public function emit($getValue = false) { return $this->token; }
    };

    $stmt = new c_statement($child);
    $out  = $stmt->emit();

    $this->assertSame("CODE;\n", $out);
  }
}
