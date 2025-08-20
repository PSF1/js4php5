<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\JS;
use js4php5\runtime\Runtime;

final class JSCallFunctionTest extends TestCase
{
  protected function setUp(): void
  {
    // Reset Runtime and init
    $reset = \Closure::bind(function () {
      Runtime::$global = null;
      Runtime::$contexts = [];
      Runtime::$functions = [];
      Runtime::$startExtender = null;
      Runtime::$idcache = [];
    }, null, Runtime::class);
    $reset();
    JS::init();
  }

  public function testCallFunctionReturnsConvertedPhpValue(): void
  {
    // Create a global function "sum" bound to PHP function "php_sum"
    if (!function_exists('php_sum')) {
      function php_sum($a, $b) {
        // Return a Base number
        return Runtime::js_int($a->toNumber()->value + $b->toNumber()->value);
      }
    }

    // Define function globally (not on an object) so that Runtime::id('sum') can resolve it
    Runtime::define_function('php_sum', 'sum', ['a', 'b']);

    // Call via JS::callFunction with PHP scalars; they are converted internally
    $out = JS::callFunction('sum', [2, 3]);

    // Expect classic PHP value (int/float) after conversion
    $this->assertSame(5.0, $out);
  }
}
