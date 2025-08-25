<?php
declare(strict_types=1);

namespace js4php5\tests\compiler\parser;

use PHPUnit\Framework\TestCase;
use js4php5\compiler\parser\state_set_labeler;

final class StateSetLabelerTest extends TestCase
{
  public function testMapPropertyIsDeclaredAndPublic(): void
  {
    $lbl = new state_set_labeler();

    $ref = new \ReflectionClass(state_set_labeler::class);
    $this->assertTrue($ref->hasProperty('map'), 'state_set_labeler::$map must be declared');
    $prop = $ref->getProperty('map');
    $this->assertTrue($prop->isPublic(), 'state_set_labeler::$map should be public');
    $this->assertIsArray($lbl->map);
  }

  public function testLabelIsStableForSameSetRegardlessOfOrder(): void
  {
    $lbl = new state_set_labeler();

    $a = ['S1', 'S2', 'S3'];
    $b = ['S3', 'S2', 'S1'];

    $la = $lbl->label($a);
    $lb = $lbl->label($b);

    // Same set should produce the same label
    $this->assertSame($la, $lb);
    $this->assertIsString($la);
    $this->assertNotSame('', $la);
  }

  public function testDifferentSetsProduceDifferentLabels(): void
  {
    $lbl = new state_set_labeler();

    $la = $lbl->label(['A']);
    $lb = $lbl->label(['B']);

    // Distinct sets should result in distinct labels
    $this->assertNotSame($la, $lb);
  }
}
