<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_kasko_content\Unit\Plugin\Block;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\helfi_kasko_content\Plugin\Block\CrossInstitutionalStudiesHeroBlock;
use Drupal\Tests\UnitTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests the CrossInstitutionalStudiesHeroBlock block plugin.
 */
#[Group('helfi_kasko_content')]
class CrossInstitutionalStudiesHeroBlockTest extends UnitTestCase {

  /**
   * The block plugin under test.
   */
  private CrossInstitutionalStudiesHeroBlock $block;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->block = new CrossInstitutionalStudiesHeroBlock(
      [],
      'cross_institutional_studies_hero_block',
      ['provider' => 'helfi_kasko_content'],
    );
    $this->block->setStringTranslation($this->getStringTranslationStub());
  }

  /**
   * Tests that build() returns the expected render array.
   */
  public function testBuild(): void {
    $build = $this->block->build();

    $this->assertArrayHasKey('#theme', $build);
    $this->assertEquals('cross_institutional_studies_hero_block', $build['#theme']);

    $this->assertArrayHasKey('#hero_title', $build);
    $this->assertInstanceOf(TranslatableMarkup::class, $build['#hero_title']);
    $this->assertStringContainsString(
      'Online and distance studies of City of Helsinki general upper secondary schools',
      (string) $build['#hero_title']
    );
  }

}
