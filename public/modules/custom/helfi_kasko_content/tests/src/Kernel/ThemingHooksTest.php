<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_kasko_content\Kernel;

use Drupal\helfi_kasko_content\Hook\ThemingHooks;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests theming hooks.
 */
#[Group('helfi_kasko_content')]
#[RunTestsInSeparateProcesses]
class ThemingHooksTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_api_base',
    'helfi_kasko_content',
    'system',
    'user',
  ];

  /**
   * Test that the theme definitions are registered.
   */
  public function testTheme(): void {
    $hook = new ThemingHooks();
    $theme = $hook->theme();

    $this->assertArrayHasKey('cross_institutional_studies', $theme);
    $this->assertArrayHasKey('cross_institutional_studies_card', $theme);
    $this->assertEquals('cross-institutional-studies-search', $theme['cross_institutional_studies_search']['template']);
    $this->assertEquals('cross-institutional-studies-hero-block', $theme['cross_institutional_studies_hero_block']['template']);
    $this->assertEquals('tpr-unit-ontologyword-details', $theme['tpr_ontologyword_details_formatter']['template']);
  }

  /**
   * Test that all search paragraphs are set with grey background.
   */
  public function testFirstParagraphGrey(): void {
    $hook = new ThemingHooks();
    $paragraphs = [];
    $hook->firstParagraphGrey($paragraphs);

    $this->assertEquals([
      'after_school_activity_search',
      'daycare_search',
      'high_school_search',
      'playground_search',
      'school_search',
      'vocational_school_search',
    ], $paragraphs);
  }

}
