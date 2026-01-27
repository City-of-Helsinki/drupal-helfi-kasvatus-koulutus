<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_kasko_content\Kernel\CrossInstitutionalStudies\Controller;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\MemoryBackend;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Url;
use Drupal\helfi_kasko_content\CrossInstitutionalStudies\Client;
use Drupal\helfi_kasko_content\Hook\CrossInstitutionalStudies;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\helfi_api_base\Traits\ApiTestTrait;
use Drupal\Tests\user\Traits\UserCreationTrait;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Group;

/**
 * Tests cross-institutional studies controller.
 */
#[Group('helfi_kasko_content')]
class ControllerTest extends KernelTestBase {

  use ApiTestTrait;
  use UserCreationTrait;

  /**
   * {@inheritDoc}
   */
  protected static $modules = [
    'helfi_kasko_content',
    'system',
    'user',
  ];

  /**
   * {@inheritDoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');

    $this->config('helfi_kasko_content.settings')
      ->set('linked_events_api_url', 'https://linkedevents.api.test.hel.ninja/v1')
      ->set('courses_max_age', 3600)
      ->save();

    DateFormat::create([
      'id' => 'short',
      'pattern' => 'j.n.Y',
    ])->save();
  }

  /**
   * Tests controller returns 200 for valid event.
   */
  public function testController(): void {
    $this->setUpCurrentUser(permissions: ['access content']);

    $client = $this->createMockHttpClient([
      new Response(404, [], '{"detail": "Not found."}'),
      new Response(200, [], $this->createEventResponse('test-event-123')),
    ]);

    $this->container->set('http_client', $client);

    $request = $this->getMockedRequest(
      Url::fromRoute('helfi_kasko_content.cross_institutional_studies', [
        'id' => 'non-existent',
      ])->toString()
    );

    // Tests controller returns 404 for non-existent event.
    $response = $this->processRequest($request);
    $this->assertEquals(404, $response->getStatusCode());

    $request = $this->getMockedRequest(
      Url::fromRoute('helfi_kasko_content.cross_institutional_studies', [
        'id' => 'test-event-123',
      ])->toString()
    );

    $response = $this->processRequest($request);
    $this->assertEquals(200, $response->getStatusCode());
    $this->assertStringContainsString('Short description for test-event-123', $response->getContent());
  }

  /**
   * Tests controller with sub-events.
   */
  public function testControllerWithSubEvents(): void {
    $this->setUpCurrentUser(permissions: ['access content']);

    $client = $this->createMockHttpClient([
      new Response(200, [], $this->createEventResponse('parent-event', ['sub-event-1', 'sub-event-2'])),
      new Response(200, [], $this->createEventResponse('sub-event-1')),
      new Response(200, [], $this->createEventResponse('sub-event-2')),
    ]);

    $this->container->set('http_client', $client);

    $request = $this->getMockedRequest(Url::fromRoute('helfi_kasko_content.cross_institutional_studies', [
      'id' => 'parent-event',
    ])->toString());

    $response = $this->processRequest($request);

    $this->assertEquals(200, $response->getStatusCode());
    $content = $response->getContent();
    $this->assertStringContainsString('Short description for parent-event', $content);
    $this->assertStringContainsString('Test Event sub-event-1', $content);
    $this->assertStringContainsString('Test Event sub-event-2', $content);
  }

  /**
   * Tests language switcher hook for custom controller.
   */
  public function testLangSwitcher(): void {
    $httpClient = $this->createMockHttpClient([
      new Response(200, [], $this->createEventResponse('test-event-123')),
    ]);

    $client = new Client(
      $httpClient,
      new MemoryBackend($this->container->get(TimeInterface::class)),
      $this->container->get(ConfigFactoryInterface::class),
    );

    $hook = new CrossInstitutionalStudies($client);

    $links = [
      'fi' => [],
      'sv' => [],
      'en' => [],
    ];

    $hook->languageSwitchLinksAlter(
      $links,
      '',
      Url::fromRoute('helfi_kasko_content.cross_institutional_studies', [
        'id' => 'test-event-123',
      ]),
    );

    // Event is missing sv translation.
    $this->assertNotEmpty($links['sv']['#untranslated']);
    $this->assertTrue(empty($links['fi']['#untranslated']));
    $this->assertTrue(empty($links['en']['#untranslated']));
  }

  /**
   * Creates a mock event response.
   */
  private function createEventResponse(string $id, array $subEvents = []): string {
    return json_encode([
      'id' => $id,
      'name' => (object) [
        'en' => "Test Event $id",
        'fi' => "Testitapahtuma $id",
      ],
      'description' => (object) [
        'en' => "Description for $id",
        'fi' => "Kuvaus $id",
      ],
      'short_description' => (object) [
        'en' => "Short description for $id",
        'fi' => "Lyhyt kuvaus $id",
      ],
      'images' => [],
      'keywords' => [
        (object) ['@id' => 'https://linkedevents.api.test.hel.ninja/v1/keyword/helsinki:contact_learning/'],
      ],
      'start_time' => '2025-01-01T10:00:00Z',
      'end_time' => '2025-06-01T16:00:00Z',
      'location_extra_info' => (object) [
        'en' => 'Location info',
        'fi' => 'Sijaintitieto',
      ],
      'sub_events' => array_map(
        static fn (string $subEventId) => (object) ['@id' => "https://linkedevents.api.test.hel.ninja/v1/event/$subEventId/"],
        $subEvents
      ),
    ]);
  }

}
