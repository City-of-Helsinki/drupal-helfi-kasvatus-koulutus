<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_kasko_content\Kernel\CrossInstitutionalStudies\Controller;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\helfi_api_base\Environment\EnvironmentEnum;
use Drupal\helfi_api_base\Environment\Project;
use Drupal\helfi_kasko_content\CrossInstitutionalStudies\Controller\SearchController;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\helfi_api_base\Traits\EnvironmentResolverTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests the cross-institutional studies search controller.
 */
#[Group('helfi_kasko_content')]
class SearchControllerTest extends KernelTestBase {

  use EnvironmentResolverTrait;

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
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->config('helfi_kasko_content.settings')
      ->set('linked_events_api_url', 'https://linkedevents.api.test.hel.ninja/v1')
      ->save();

    $this->setActiveProject(Project::KASVATUS_KOULUTUS, EnvironmentEnum::Local);
  }

  /**
   * Tests that title() returns the expected translatable markup.
   */
  public function testTitle(): void {
    $controller = $this->container->get(SearchController::class);
    $title = $controller->title();

    $this->assertInstanceOf(TranslatableMarkup::class, $title);
    $this->assertStringContainsString(
      'Online and distance studies of City of Helsinki general upper secondary schools',
      (string) $title
    );
  }

  /**
   * Tests that content() returns the expected render array structure.
   */
  public function testContent(): void {
    $controller = $this->container->get(SearchController::class);
    $build = $controller->content();

    $this->assertEquals('cross_institutional_studies_search', $build['#theme']);

    $drupalSettings = $build['#attached']['drupalSettings'];
    $this->assertTrue($drupalSettings['useExperimentalGhosts']);

    $eventsSettings = $drupalSettings['helfi_events'];
    $this->assertNotEmpty($eventsSettings['baseUrls']);

    $searchData = $eventsSettings['data']['cross-institutional-studies-search'];
    $this->assertStringContainsString('linkedevents.api.test.hel.ninja', $searchData['events_api_url']);
    $this->assertStringContainsString('event_type=Course', $searchData['events_api_url']);
    $this->assertStringContainsString('super_event', $searchData['events_api_url']);
    $this->assertEquals(10, $searchData['field_event_count']);
    $this->assertTrue($searchData['useCrossInstitutionalStudiesForm']);
  }

}
