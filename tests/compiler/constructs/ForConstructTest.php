<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\compiler\constructs\c_for;
use js4php5\compiler\constructs\BaseConstruct;
use js4php5\compiler\constructs\c_block;

/**
 * Test for c_for emit() behavior.
 */
final class ForConstructTest extends TestCase
{
  public function setUp(): void
  {
    // Ensure nesting static property exists and is reset for tests.
    if (!class_exists(\js4php5\compiler\constructs\c_source::class)) {
      // If the class is not autoloadable, create a minimal stub to avoid fatal errors in tests.
      eval('namespace js4php5\compiler\constructs; class c_source { public static $nest = 0; }');
    } else {
      \js4php5\compiler\constructs\c_source::$nest = 0;
    }
  }

  public function testEmitProducesExpectedForString()
  {
    // Mock the init (c_var or c_assign) which produces initialization code.
    $initMock = $this->getMockBuilder(stdClass::class)
      ->setMethods(['emit'])
      ->getMock();
    $initMock->expects($this->once())
      ->method('emit')
      ->with(true)
      ->willReturn("var i = 0;");

    // Mock the condition (BaseBinaryConstruct or c_call).
    $conditionMock = $this->getMockBuilder(stdClass::class)
      ->setMethods(['emit'])
      ->getMock();
    $conditionMock->expects($this->once())
      ->method('emit')
      ->with(true)
      ->willReturn("i < 10");

    // Mock the increment (BaseConstruct).
    $incrementMock = $this->getMockBuilder(stdClass::class)
      ->setMethods(['emit'])
      ->getMock();
    $incrementMock->expects($this->once())
      ->method('emit')
      ->with(true)
      ->willReturn("i++");

    // Mock the statement (c_block) which returns the block body.
    $statementMock = $this->getMockBuilder(c_block::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['emit'])
      ->getMock();
    $statementMock->expects($this->once())
      ->method('emit')
      ->with(true)
      ->willReturn("    echo i;\n");

    // Instantiate c_for with these mocks.
    $forConstruct = new c_for($initMock, $conditionMock, $incrementMock, $statementMock);

    // Build expected output:
    // Note: init code appears before the for(...) header.
    $expected = "var i = 0;for (;Runtime::js_bool(i < 10);i++) {\n    echo i;\n\n}\n";

    $this->assertSame($expected, $forConstruct->emit(true));
  }
}
