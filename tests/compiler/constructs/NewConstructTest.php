<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_call;
use js4php5\compiler\constructs\c_new;

/**
 * Stub for a constructor expression; devuelve un token y marca si se pidió valor.
 */
class NewCtorStub extends BaseConstruct
{
  private string $token;
  public function __construct(string $token) { $this->token = $token; }
  public function emit($getValue = false)
  {
    // El constructor debe emitirse por valor
    return $this->token . ($getValue ? '_gv' : '');
  }
}

/**
 * Stub para argumentos: marca getValue=true con sufijo.
 */
class NewArgStub extends BaseConstruct
{
  private string $token;
  public function __construct(string $token) { $this->token = $token; }
  public function emit($getValue = false)
  {
    return $this->token . ($getValue ? '_gv' : '');
  }
}

final class NewConstructTest extends TestCase
{
  public function testEmitWithExpressionOnlyBuildsNewWithEmptyArgs(): void
  {
    $ctor = new NewCtorStub('CTOR');
    $node = new c_new($ctor);

    $php = $node->emit();

    // Espera Runtime::_new(CTOR_gv, array())
    $this->assertSame('Runtime::_new(CTOR_gv, array())', $php);
  }

  public function testEmitVampirizesInlineCallExtractingCalleeAndArgs(): void
  {
    $callee = new NewCtorStub('CTOR');
    $a1 = new NewArgStub('A1');
    $a2 = new NewArgStub('A2');

    // c_call(callee, [args...])
    $call = new c_call($callee, [$a1, $a2]);

    // c_new detecta c_call y toma expr/args de él
    $node = new c_new($call);
    $php  = $node->emit();

    // Espera Runtime::_new(CTOR_gv, array(A1_gv,A2_gv))
    $this->assertSame('Runtime::_new(CTOR_gv, array(A1_gv,A2_gv))', $php);
  }

  public function testConstructorAcceptsNonArrayArgsFromCallSafely(): void
  {
    $callee = new NewCtorStub('C');
    $arg    = new NewArgStub('X');

    // c_call con un solo argumento no array
    $call = new c_call($callee, $arg);

    $node = new c_new($call);
    $php  = $node->emit();

    $this->assertSame('Runtime::_new(C_gv, array(X_gv))', $php);
  }
}
