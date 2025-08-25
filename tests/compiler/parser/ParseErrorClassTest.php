<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\parser;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\parser\parse_error;

final class ParseErrorClassTest extends TestCase
{
  public function testExtendsBaseExceptionAndCarriesMessageAndCode(): void
  {
    $e = new parse_error('parser failed', 13);

    $this->assertInstanceOf(\Exception::class, $e);
    $this->assertSame('parser failed', $e->getMessage());
    $this->assertSame(13, $e->getCode());
  }

  public function testIsDistinctFromPhpBuiltinParseError(): void
  {
    $e = new parse_error('x');
    $this->assertNotInstanceOf(\ParseError::class, $e);

    try {
      // Throw built-in PHP ParseError and ensure it is not caught by our custom class
      throw new \ParseError('builtin');
    } catch (parse_error $wrongCatch) {
      $this->fail('Custom js4php5\\compiler\\parser\\parse_error should not catch PHP\\ParseError');
    } catch (\ParseError $rightCatch) {
      $this->assertSame('builtin', $rightCatch->getMessage());
    }
  }
}
