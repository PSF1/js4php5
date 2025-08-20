<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\constructs;

use js4php5\compiler\constructs\BaseConstruct;

/**
 * Minimal subclass to exercise BaseConstruct::className().
 */
class FakeConstruct extends BaseConstruct
{
  // Return a fixed string to satisfy abstract contract
  public function emit($getValue = false): string
  {
    // This stub is only used for className() testing
    return 'OK';
  }
}
