<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\runtime\jsException;
use js4php5\runtime\Base;
use js4php5\runtime\Runtime;

final class jsExceptionTest extends TestCase
{
  public function testConstructSetsTypeAndValue(): void
  {
    $value = new Base(Base::STRING, 'boom');
    $e = new jsException($value);

    $this->assertInstanceOf(\Exception::class, $e);
    $this->assertSame(jsException::EXCEPTION, $e->type);
    $this->assertSame($value, $e->value);
  }

  public function testCanBeCaughtAsException(): void
  {
    $value = new Base(Base::NUMBER, 42.0);

    try {
      throw new jsException($value);
    } catch (jsException $e) {
      $this->assertSame(42.0, $e->value->value);
      $this->assertSame(jsException::EXCEPTION, $e->type);
    }
  }
}
