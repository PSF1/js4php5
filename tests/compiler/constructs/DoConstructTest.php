<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\c_do;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_block;
use js4php5\compiler\constructs\c_source;

/**
 * Test for c_do emit() behavior.
 */
final class DoConstructTest extends TestCase
{
  public function setUp(): void
  {
    // Ensure nesting static property exists and is reset for tests.
    if (!class_exists(c_source::class)) {
      // If the class is not autoloadable, create a minimal stub to avoid fatal errors in tests.
      eval('namespace js4php5\compiler\constructs; class c_source { public static $nest = 0; }');
    } else {
      c_source::$nest = 0;
    }
  }

  public function testEmitProducesExpectedDoWhileString()
  {
    // Create a mock for the statement (c_block) that will return a statement block string.
    $statementMock = $this->getMockBuilder(c_block::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['emit'])
      ->getMock();

    $statementMock->expects($this->once())
      ->method('emit')
      ->with(true)
      ->willReturn("{ echo 'hi'; } ");

    // Create a mock for the expression (BaseConstruct) that will return an expression string.
    $exprMock = $this->getMockBuilder(BaseConstruct::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['emit'])
      ->getMock();

    $exprMock->expects($this->once())
      ->method('emit')
      ->with(true)
      ->willReturn("expr_code");

    // Instantiate c_do with the mocks.
    $doConstruct = new c_do($exprMock, $statementMock);

    // Emit and assert the exact expected output (note the rtrim on the statement removes trailing space).
    $expected = "do { echo 'hi'; } while (Runtime::js_bool(expr_code));\n";
    $this->assertSame($expected, $doConstruct->emit(true));
  }
}
