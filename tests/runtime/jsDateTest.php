<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\JS;
use js4php5\runtime\Runtime;
use js4php5\runtime\jsDate;
use js4php5\runtime\Base;
use js4php5\runtime\jsRef;

final class jsDateTest extends TestCase
{
  protected function setUp(): void
  {
    // Reset Runtime and init
    $reset = \Closure::bind(function () {
      Runtime::$global = null;
      Runtime::$contexts = [];
      Runtime::$functions = [];
      Runtime::$zero = null;
      Runtime::$one = null;
      Runtime::$true = null;
      Runtime::$false = null;
      Runtime::$null = null;
      Runtime::$undefined = null;
      Runtime::$nan = null;
      Runtime::$infinity = null;
      Runtime::$exception = null;
      Runtime::$sortfn = null;
      Runtime::$proto_object = null;
      Runtime::$proto_function = null;
      Runtime::$proto_array = null;
      Runtime::$proto_string = null;
      Runtime::$proto_boolean = null;
      Runtime::$proto_number = null;
      Runtime::$proto_date = null;
      Runtime::$proto_regexp = null;
      Runtime::$proto_error = null;
      Runtime::$proto_evalerror = null;
      Runtime::$proto_rangeerror = null;
      Runtime::$proto_referenceerror = null;
      Runtime::$proto_syntaxerror = null;
      Runtime::$proto_typeerror = null;
      Runtime::$proto_urierror = null;
      Runtime::$startExtender = null;
      Runtime::$idcache = [];
    }, null, Runtime::class);
    $reset();

    JS::init();
  }

  private function callMethod(object $obj, string $method, array $args = [])
  {
    $ref = new jsRef($obj, $method);
    return Runtime::call($ref, $args);
  }

  public function testNowConstructorValueAndToString(): void
  {
    $d = new jsDate(); // "now"
    $this->assertIsFloat($d->value);
    $this->assertGreaterThan(0, $d->value);

    // toString returns a non-empty string (RFC2822 style)
    $s = $this->callMethod($d, 'toString', []);
    $this->assertSame(Base::STRING, $s->type);
    $this->assertNotSame('', $s->value);

    // getTime/valueOf should return milliseconds (float) wrapped
    $t1 = $this->callMethod($d, 'getTime', []);
    $t2 = $this->callMethod($d, 'valueOf', []);
    $this->assertSame(Base::NUMBER, $t1->type);
    $this->assertSame(Base::NUMBER, $t2->type);
    $this->assertEqualsWithDelta($d->value, $t1->value, 5_000.0); // within 5 seconds
    $this->assertEqualsWithDelta($t1->value, $t2->value, 1e-6);
  }

  public function testParseAndUTC(): void
  {
    // Use a fixed UTC string to avoid local timezone variance
    $str = '2000-01-02T03:04:05Z'; // ISO-8601 UTC
    $ms = jsDate::parse(Runtime::js_str($str));
    $this->assertSame(Base::NUMBER, $ms->type);

    // Compute expected ms since epoch in UTC
    $expected = (new DateTimeImmutable($str))->getTimestamp() * 1000.0;
    $this->assertSame($expected, $ms->value);

    // jsDate::UTC(year, month(0-based), date, hours, minutes, seconds, ms)
    $utc = jsDate::UTC(
      Runtime::js_int(2000),
      Runtime::js_int(0),
      Runtime::js_int(2),
      Runtime::js_int(3),
      Runtime::js_int(4),
      Runtime::js_int(5),
      Runtime::js_int(0)
    );
    $this->assertSame($expected, $utc->value);
  }

  public function testDateParts(): void
  {
    $d = new jsDate(
      Runtime::js_int(2000),
      Runtime::js_int(0),
      Runtime::js_int(2),
      Runtime::js_int(3),
      Runtime::js_int(4),
      Runtime::js_int(5),
      Runtime::js_int(678)
    );

    $year = $this->callMethod($d, 'getFullYear', []);
    $utcYear = $this->callMethod($d, 'getUTCFullYear', []);
    $this->assertSame(2000.0, $year->value);
    $this->assertSame(2000.0, $utcYear->value);

    $day = $this->callMethod($d, 'getDay', []);
    $utcDay = $this->callMethod($d, 'getUTCDay', []);
    $this->assertGreaterThanOrEqual(0.0, $day->value);
    $this->assertLessThanOrEqual(6.0, $day->value);
    $this->assertGreaterThanOrEqual(0.0, $utcDay->value);
    $this->assertLessThanOrEqual(6.0, $utcDay->value);

    $ms = $this->callMethod($d, 'getUTCMilliseconds', []);
    $this->assertGreaterThanOrEqual(0.0, $ms->value);
    $this->assertLessThan(1000.0, $ms->value);

    $offset = $this->callMethod($d, 'getTimezoneOffset', []);
    $this->assertSame(Base::NUMBER, $offset->type);
    // offset in minutes, can be negative or positive
    $this->assertIsFloat($offset->value);
  }

  public function testLocaleStringsReturnStrings(): void
  {
    $d = new jsDate();
    foreach (['toLocaleString','toLocaleDateString','toLocaleTimeString'] as $m) {
      $s = $this->callMethod($d, $m, []);
      $this->assertSame(Base::STRING, $s->type);
      $this->assertNotSame('', $s->value);
    }

    $utc = $this->callMethod($d, 'toUTCString', []);
    $this->assertSame(Base::STRING, $utc->type);
    $this->assertNotSame('', $utc->value);
    $this->assertStringContainsString('GMT', $utc->value);
  }

  public function testGetMillisecondsAndAlias(): void
  {
    $d = new jsDate(
      Runtime::js_int(2000),
      Runtime::js_int(0),
      Runtime::js_int(2),
      Runtime::js_int(3),
      Runtime::js_int(4),
      Runtime::js_int(5),
      Runtime::js_int(678)
    );

    $ms = $this->callMethod($d, 'getMilliseconds', []);
    $alias = $this->callMethod($d, 'getMillieconds', []);

    $this->assertSame(Base::NUMBER, $ms->type);
    $this->assertGreaterThanOrEqual(0.0, $ms->value);
    $this->assertLessThan(1000.0, $ms->value);

    $this->assertSame($ms->value, $alias->value);
  }
}
