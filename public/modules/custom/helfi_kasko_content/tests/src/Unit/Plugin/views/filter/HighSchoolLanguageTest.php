<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_kasko_content\Unit\Plugin\views\filter;

use Drupal\helfi_kasko_content\Plugin\views\filter\HighSchoolLanguage;
use Drupal\Tests\UnitTestCase;

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

}
