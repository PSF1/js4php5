<?php
declare(strict_types=1);

namespace js4php5\tests\compiler;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\jsly;

/**
 * Basic tests for the jsly static tables and semantic action lambdas.
 *
 * These tests are not exhaustive but exercise:
 *  - presence of lexing & parsing tables
 *  - representative lambda functions:
 *    - array/sequence operations
 *    - scalar conversions and reference mutation lambdas
 *    - creation of several construct objects (type assertions)
 */
final class JslyTest extends TestCase
{
  public function testLexpAndDpaMinimalStructure(): void
  {
    // Ensure lexp and dpa are present and contain expected subkeys.
    $this->assertIsArray(jsly::$lexp);
    $this->assertArrayHasKey('INITIAL', jsly::$lexp);
    $this->assertArrayHasKey('text', jsly::$lexp);

    // A sanity check: the INITIAL lex rules include a T_FUNCTION entry at index 5.
    $this->assertArrayHasKey(5, jsly::$lexp['INITIAL']);
    $this->assertSame('T_FUNCTION', jsly::$lexp['INITIAL'][5][1]);

    // dpa start map contains c_program mapping to a state string.
    $this->assertIsArray(jsly::$dpa);
    $this->assertArrayHasKey('start', jsly::$dpa);
    $this->assertArrayHasKey('c_program', jsly::$dpa['start']);
    $this->assertStringStartsWith('s', jsly::$dpa['start']['c_program']);
  }

  public function testLambdaArrayAndTokenPassthrough(): void
  {
    // __lambda_35: returns array([$tokens[0]])
    $out = jsly::__lambda_35(['foo']);
    $this->assertIsArray($out);
    $this->assertSame(['foo'], $out);

    // __lambda_36: appends tokens[2] to tokens[0] (assumed tokens[0] is array)
    $tokens = [['a'], null, 'b'];
    $res = jsly::__lambda_36($tokens);
    $this->assertSame(['a', 'b'], $res);

    // __lambda_37: returns tokens[0] unchanged
    $this->assertSame('X', jsly::__lambda_37(['X']));
  }

  public function testLambdaSimpleUtilityAndFlags(): void
  {
    // __lambda_16: implodes array of strings
    $this->assertSame('abc', jsly::__lambda_16(['a', 'b', 'c']));

    // __lambda_19 returns empty string
    $this->assertSame('', jsly::__lambda_19([]));

    // __lambda_85 true and __lambda_86 false
    $this->assertTrue(jsly::__lambda_85([]));
    $this->assertFalse(jsly::__lambda_86([]));
  }

  public function testLambdaReferenceMutatorsAndConversions(): void
  {
    // __lambda_21 sets state = 'text' (signature uses references)
    $type = 'T';
    $text = 'x';
    $match = null;
    $state = 'INITIAL';
    $context = 0;
    jsly::__lambda_21($type, $text, $match, $state, $context);
    $this->assertSame('text', $state);

    // __lambda_24 sets state = 'INITIAL'
    $state = 'OTHER';
    jsly::__lambda_24($type, $text, $match, $state, $context);
    $this->assertSame('INITIAL', $state);

    // __lambda_22: hexdec conversion of text ("0x10" -> 16)
    $type = null;
    $text = '0x10';
    jsly::__lambda_22($type, $text, $match, $state, $context);
    $this->assertSame(16, $text);

    // __lambda_23: numeric coercion ("3.5" -> 3.5)
    $text = '3.5';
    jsly::__lambda_23($type, $text, $match, $state, $context);
    $this->assertSame(3.5, $text);
  }

  public function testSomeLambdaConstructCreatorsReturnConstructInstances(): void
  {
    // Many lambdas return instances of classes in compiler\constructs.
    // We test a representative sample to ensure the lambdas are wiring correctly.

    // __lambda_82 -> c_literal_null
    $nul = jsly::__lambda_82([]);
    $this->assertInstanceOf(\js4php5\compiler\constructs\c_literal_null::class, $nul);

    // __lambda_83 -> c_literal_boolean
    $btrue = jsly::__lambda_83(['true']);
    $this->assertInstanceOf(\js4php5\compiler\constructs\c_literal_boolean::class, $btrue);

    // __lambda_84 -> c_literal_number
    $num = jsly::__lambda_84(['123']);
    $this->assertInstanceOf(\js4php5\compiler\constructs\c_literal_number::class, $num);

    // __lambda_87 -> c_literal_string
    $s = jsly::__lambda_87(['hello']);
    $this->assertInstanceOf(\js4php5\compiler\constructs\c_literal_string::class, $s);

    // __lambda_99 -> c_accessor (tokens[0], tokens[2], 1)
    $acc = jsly::__lambda_99(['obj', null, 'prop']);
    $this->assertInstanceOf(\js4php5\compiler\constructs\c_accessor::class, $acc);

    // __lambda_102 -> c_call
    $call = jsly::__lambda_102(['fn', ['arg1']]);
    $this->assertInstanceOf(\js4php5\compiler\constructs\c_call::class, $call);

    // __lambda_120 -> c_plus
    $plus = jsly::__lambda_120(['left', null, 'right']);
    $this->assertInstanceOf(\js4php5\compiler\constructs\c_plus::class, $plus);
  }

  public function testLambdaPropertyPairAndSplitHelpers(): void
  {
    // __lambda_93: returns array($tokens[0], $tokens[2])
    $out = jsly::__lambda_93(['k:v', null, 'value']);
    $this->assertSame(['k:v', 'value'], $out);

    // __lambda_94: splits tokens[0] by ':' and returns [c_literal_string(key,0), tokens[1]].
    $p = jsly::__lambda_94(['key:rest', 'VALUE']);
    $this->assertIsArray($p);
    $this->assertInstanceOf(\js4php5\compiler\constructs\c_literal_string::class, $p[0]);
    $this->assertSame('VALUE', $p[1]);
  }
}
