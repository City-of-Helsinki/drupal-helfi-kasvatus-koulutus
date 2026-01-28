<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_kasko_content\Unit\Plugin\views\filter;

use Drupal\helfi_kasko_content\Plugin\views\filter\HighSchoolLanguage;
use Drupal\Tests\UnitTestCase;
use Drupal\views\Plugin\views\query\Sql;

/**
 * Tests the HighSchoolLanguage views filter plugin.
 *
 * @coversDefaultClass \Drupal\helfi_kasko_content\Plugin\views\filter\HighSchoolLanguage
 * @group helfi_kasko_content
 */
class HighSchoolLanguageTest extends UnitTestCase {

  /**
   * The filter plugin under test.
   *
   * @var \Drupal\helfi_kasko_content\Plugin\views\filter\HighSchoolLanguage
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
   *
   * @covers ::generateOptions
   */
  public function testGenerateOptions(): void {
    $options = $this->filter->generateOptions();

    $this->assertIsArray($options);
    $this->assertArrayHasKey('fi', $options);
    $this->assertArrayHasKey('sv', $options);
    $this->assertArrayHasKey('en', $options);
    $this->assertCount(3, $options);
    $this->assertEquals('Finnish', $options['fi']);
    $this->assertEquals('Swedish', $options['sv']);
    $this->assertEquals('English', $options['en']);
  }

  /**
   * Tests query method with Swedish language selected.
   *
   * @covers ::query
   */
  public function testQueryWithSwedish(): void {
    $query = $this->createMock(Sql::class);
    $query->expects($this->once())
      ->method('addWhere')
      ->with(
        $this->anything(),
        'tpr_unit_field_data.id',
        [7051, 6820, 6901],
        'IN'
      );

    $this->filter->query = $query;
    $this->filter->value = ['sv'];
    $this->filter->options['group'] = 1;
    $this->filter->query();
  }

  /**
   * Tests query method with English language selected.
   *
   * @covers ::query
   */
  public function testQueryWithEnglish(): void {
    $query = $this->createMock(Sql::class);
    $query->expects($this->once())
      ->method('addWhere')
      ->with(
        $this->anything(),
        'tpr_unit_field_data.id',
        [34442, 30863],
        'IN'
      );

    $this->filter->query = $query;
    $this->filter->value = ['en'];
    $this->filter->options['group'] = 1;
    $this->filter->query();
  }

  /**
   * Tests query method with multiple languages selected.
   *
   * @covers ::query
   */
  public function testQueryWithMultipleLanguages(): void {
    $query = $this->createMock(Sql::class);
    $query->expects($this->once())
      ->method('addWhere')
      ->with(
        $this->anything(),
        'tpr_unit_field_data.id',
        [7051, 6820, 6901, 34442, 30863],
        'IN'
      );

    $this->filter->query = $query;
    $this->filter->value = ['sv', 'en'];
    $this->filter->options['group'] = 1;
    $this->filter->query();
  }

  /**
   * Tests query method with empty value.
   *
   * @covers ::query
   */
  public function testQueryWithEmptyValue(): void {
    $query = $this->createMock(Sql::class);
    $query->expects($this->never())
      ->method('addWhere');

    $this->filter->query = $query;
    $this->filter->value = [];
    $this->filter->query();
  }

  /**
   * Tests query method with Finnish language (empty IDs).
   *
   * @covers ::query
   */
  public function testQueryWithFinnish(): void {
    $query = $this->createMock(Sql::class);
    $query->expects($this->never())
      ->method('addWhere');

    $this->filter->query = $query;
    $this->filter->value = ['fi'];
    $this->filter->options['group'] = 1;
    $this->filter->query();
  }

}
