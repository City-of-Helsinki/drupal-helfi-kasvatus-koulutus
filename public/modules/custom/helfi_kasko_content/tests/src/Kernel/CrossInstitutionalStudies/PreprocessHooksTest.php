<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_kasko_content\Kernel\CrossInstitutionalStudies;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\helfi_kasko_content\CrossInstitutionalStudies\Client;
use Drupal\helfi_kasko_content\Hook\CrossInstitutionalStudies;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests cross-institutional studies preprocess hooks.
 */
#[Group('helfi_kasko_content')]
#[RunTestsInSeparateProcesses]
class PreprocessHooksTest extends KernelTestBase {

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
   * Create the hook with a route match that returns the given route name.
   */
  private function createHook(string $routeName): CrossInstitutionalStudies {
    $routeMatch = $this->createMock(RouteMatchInterface::class);
    $routeMatch->method('getRouteName')->willReturn($routeName);

    return new CrossInstitutionalStudies(
      $this->createMock(Client::class),
      $routeMatch,
    );
  }

  /**
   * Test that the preprocess_page sets has_hero.
   */
  public function testPreprocessPage(): void {
    $hook = $this->createHook('helfi_kasko_content.cross_institutional_studies_search');
    $variables = [];
    $hook->preprocessPage($variables);
    $this->assertTrue($variables['has_hero']);

    $hook = $this->createHook('some.other.route');
    $variables = [];
    $hook->preprocessPage($variables);
    $this->assertArrayNotHasKey('has_hero', $variables);
  }

  /**
   * Test that the preprocess_html sets the summer theme color.
   */
  public function testPreprocessHtml(): void {
    $hook = $this->createHook('helfi_kasko_content.cross_institutional_studies_search');
    $variables = [];
    $hook->preprocessHtml($variables);
    $this->assertEquals('summer', $variables['theme_color']);

    $hook = $this->createHook('some.other.route');
    $variables = [];
    $hook->preprocessHtml($variables);
    $this->assertArrayNotHasKey('theme_color', $variables);
  }

}
