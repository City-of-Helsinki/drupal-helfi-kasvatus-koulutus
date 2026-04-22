<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_kasko_content\Unit\Plugin\views\filter;

use Drupal\helfi_kasko_content\Plugin\views\filter\HighSchoolLanguage;
use Drupal\Tests\UnitTestCase;
use Drupal\views\Plugin\views\query\Sql;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests the HighSchoolLanguage views filter plugin.
 *
 * @coversDefaultClass \Drupal\helfi_kasko_content\Plugin\views\filter\HighSchoolLanguage
 */
#[Group('helfi_kasko_content')]
class HighSchoolLanguageTest extends UnitTestCase {

  /**
   * The filter plugin under test.
   */
  protected HighSchoolLanguage $filter;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->filter = new HighSchoolLanguage([], 'high_school_language', []);
    $this->filter->setStringTranslation($this->getStringTranslationStub());
  }

  /**
   * Tests that generateOptions returns the expected language options.
   */
  public function testGenerateOptions(): void {
    $options = $this->filter->generateOptions();

    $this->assertIsArray($options);
    $this->assertArrayHasKey('fi', $options);
    $this->assertArrayHasKey('sv', $options);
    $this->assertArrayHasKey('en', $options);
    $this->assertCount(3, $options);
  }

  /**
   * Tests query method with Swedish language selected.
   */
  public function testQueryWithSwedish(): void {
    $capturedIds = [];
    $query = $this->createMock(Sql::class);
    $query->expects($this->once())
      ->method('addWhere')
      ->with(
        $this->anything(),
        'tpr_unit_field_data.id',
        $this->callback(function ($ids) use (&$capturedIds) {
          $capturedIds = $ids;
          return TRUE;
        }),
        'IN'
      );

    $this->filter->query = $query;
    $this->filter->value = ['sv'];
    $this->filter->options['group'] = 1;
    $this->filter->query();

    $this->assertCount(3, $capturedIds);
    $this->assertContains(7051, $capturedIds);
    $this->assertContains(6820, $capturedIds);
    $this->assertContains(6901, $capturedIds);
  }

  /**
   * Tests query method with English language selected.
   */
  public function testQueryWithEnglish(): void {
    $capturedIds = [];
    $query = $this->createMock(Sql::class);
    $query->expects($this->once())
      ->method('addWhere')
      ->with(
        $this->anything(),
        'tpr_unit_field_data.id',
        $this->callback(function ($ids) use (&$capturedIds) {
          $capturedIds = $ids;
          return TRUE;
        }),
        'IN'
      );

    $this->filter->query = $query;
    $this->filter->value = ['en'];
    $this->filter->options['group'] = 1;
    $this->filter->query();

    $this->assertCount(2, $capturedIds);
    $this->assertContains(34442, $capturedIds);
    $this->assertContains(30863, $capturedIds);
  }

  /**
   * Tests query method with empty value.
   */
  public function testQueryWithEmptyValue(): void {
    $query = $this->createMock(Sql::class);
    $query->expects($this->never())
      ->method('addWhere');

    $this->filter->query = $query;
    $this->filter->value = [];
    $this->assertEmpty($this->filter->value);
    $this->filter->query();
  }

  /**
   * Tests query method with Finnish language selected.
   */
  public function testQueryWithFinnish(): void {
    $capturedIds = [];
    $query = $this->createMock(Sql::class);
    $query->expects($this->once())
      ->method('addWhere')
      ->with(
        $this->anything(),
        'tpr_unit_field_data.id',
        $this->callback(function ($ids) use (&$capturedIds) {
          $capturedIds = $ids;
          return TRUE;
        }),
        'IN'
      );

    $this->filter->query = $query;
    $this->filter->value = ['fi'];
    $this->filter->options['group'] = 1;
    $this->filter->query();

    $this->assertCount(12, $capturedIds);
    $this->assertContains(15061, $capturedIds);
    $this->assertContains(73120, $capturedIds);
  }

  /**
   * Tests query method with multiple languages selected.
   */
  public function testQueryWithMultipleLanguages(): void {
    $capturedIds = [];
    $query = $this->createMock(Sql::class);
    $query->expects($this->once())
      ->method('addWhere')
      ->with(
        $this->anything(),
        'tpr_unit_field_data.id',
        $this->callback(function ($ids) use (&$capturedIds) {
          $capturedIds = $ids;
          return TRUE;
        }),
        'IN'
      );

    $this->filter->query = $query;
    $this->filter->value = ['sv', 'en'];
    $this->filter->options['group'] = 1;
    $this->filter->query();

    $this->assertCount(5, $capturedIds);
    $this->assertContains(7051, $capturedIds);
    $this->assertContains(34442, $capturedIds);
  }

}
