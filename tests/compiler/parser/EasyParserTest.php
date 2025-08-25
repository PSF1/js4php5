<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\parser;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\parser\EasyParser;

final class EasyParserTest extends TestCase
{
  public function testHasPublicStartPropertyWithDefaultNull(): void
  {
    $ref = new \ReflectionClass(EasyParser::class);

    // Ensure property is declared on the class (not created dynamically)
    $this->assertTrue($ref->hasProperty('start'), 'EasyParser::$start property must be declared');

    $prop = $ref->getProperty('start');
    $this->assertTrue($prop->isPublic(), 'EasyParser::$start should be public');

    // Default value should be null on a fresh instance
    // We cannot construct a real Parser here, so instantiate without invoking constructor
    $instance = $ref->newInstanceWithoutConstructor();
    $this->assertNull($instance->start);
  }
}
