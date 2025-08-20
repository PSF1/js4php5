<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use js4php5\JS;

final class JSClassMetaTest extends TestCase
{
  public function testCompileScriptSetsIdAndClassNames(): void
  {
    $code = JS::compileScript('/* noop */', 'my-test-id');

    // The compile step should set current script metadata
    $this->assertSame('my-test-id', JS::getCurrentScriptId());
    $className = JS::getCurrentScriptClassName();
    $this->assertNotEmpty($className);
    $this->assertStringStartsWith('js4php5_', $className);

    $fqcn = JS::getCurrentScriptFQCN();
    $this->assertStringContainsString('\\' . $className, $fqcn);
    $this->assertStringContainsString('namespace', $code);
    $this->assertStringContainsString('class ' . $className, $code);
  }

  public function testSetCacheDirTrimsTrailingSlashAndValidates(): void
  {
    $tmpBase = sys_get_temp_dir() . '/js4php5_test_cache_' . uniqid();
    $dirWithSlash = $tmpBase . '/';

    // Create directory and set as cache dir
    $this->assertTrue(@mkdir($tmpBase, 0777, true) || is_dir($tmpBase));
    $set = JS::setCacheDir($dirWithSlash);

    // Should return the trimmed path, not false
    $this->assertIsString($set);
    $this->assertSame($tmpBase, $set);

    // Cleanup
    @rmdir($tmpBase);
  }

  public function testSetCacheDirInvalidReturnsFalse(): void
  {
    // Point to a path that cannot exist or is not writable
    $invalid = '/path/that/does/not/exist/' . uniqid();
    $set = JS::setCacheDir($invalid);
    $this->assertFalse($set);
  }
}
