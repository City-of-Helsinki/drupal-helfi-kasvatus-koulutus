<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_kasko_content\Kernel\EventSubscriber;

use Drupal\Core\State\StateInterface;
use Drupal\elasticsearch_connector\Event\IndexParamsEvent;
use Drupal\helfi_kasko_content\EventSubscriber\KaskoElasticIndexSubscriber;
use Drupal\helfi_kasko_content\SchoolUtility;
use Drupal\helfi_tpr\Entity\OntologyWordDetails;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the KaskoElasticIndexSubscriber.
 */
#[Group('helfi_kasko_content')]
#[RunTestsInSeparateProcesses]
#[CoversClass(KaskoElasticIndexSubscriber::class)]
class KaskoElasticIndexSubscriberTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_kasko_content',
    'helfi_tpr',
    'helfi_api_base',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('tpr_ontology_word_details');
    $this->installEntitySchema('user');

    $this->container->get(StateInterface::class)->set(
      SchoolUtility::COMPREHENSIVE_SCHOOL_YEAR_KEY,
      '2025-2026',
    );

    // Entity with ontologyword_id=2 (in range 1-3, gets clarifications).
    $entityWithClarification = OntologyWordDetails::create([
      'id' => 101,
      'name' => 'Detail 101',
      'ontologyword_id' => '2',
      'detail_items' => [
        [
          'schoolyear' => '2025-2026',
          'clarification' => 'Current year',
        ],
        [
          'schoolyear' => '2024-2025',
          'clarification' => 'Old year',
        ],
      ],
    ]);
    $entityWithClarification->save();

    // Entity with ontologyword_id=5 (outside 1-3, no clarifications).
    $entityWithoutClarification = OntologyWordDetails::create([
      'id' => 201,
      'name' => 'Detail 201',
      'ontologyword_id' => '5',
      'detail_items' => [
        [
          'schoolyear' => '2025-2026',
          'clarification' => 'Should not appear',
        ],
      ],
    ]);
    $entityWithoutClarification->save();

    // Entity with no matching school year.
    $entityNoMatch = OntologyWordDetails::create([
      'id' => 301,
      'name' => 'Detail 301',
      'ontologyword_id' => '1',
      'detail_items' => [
        [
          'schoolyear' => '2024-2025',
          'clarification' => 'Old',
        ],
      ],
    ]);
    $entityNoMatch->save();
  }

  /**
   * Tests filtering, clarifications, and school year matching.
   */
  public function testModifyOntologywordDetailsFields(): void {
    $sut = $this->container->get(KaskoElasticIndexSubscriber::class);

    // Tests that non-schools index is ignored.
    $params = ['body' => [['some_field' => 'value']]];
    $event = new IndexParamsEvent('not_schools', $params, 'schools');

    $sut->modifyOntologywordDetailsFields($event);
    $this->assertEquals($params, $event->getParams());

    // Test: body without ontologyword_details_clarifications
    // is unchanged.
    $params = [
      'body' => [
        [
          'some_field' => 'value',
          'search_api_language' => ['fi'],
        ],
      ],
    ];
    $event = new IndexParamsEvent('schools', $params, 'schools');
    $sut->modifyOntologywordDetailsFields($event);
    $this->assertEquals($params, $event->getParams());

    // Test: ontologyword_id in 1-3 range gets clarifications.
    $event = new IndexParamsEvent('schools', [
      'body' => [
        [
          'search_api_language' => ['fi'],
          'ontologyword_details_clarifications' => [101],
        ],
      ],
    ], 'schools');
    $sut->modifyOntologywordDetailsFields($event);
    $result = $event->getParams();

    $this->assertEquals(['2'], $result['body'][0]['ontologyword_ids']);
    $this->assertEquals(
      ['Current year'],
      $result['body'][0]['ontologyword_details_clarifications'],
    );

    // Test: ontologyword_id outside 1-3 gets no clarifications.
    $event = new IndexParamsEvent('schools', [
      'body' => [
        [
          'search_api_language' => ['fi'],
          'ontologyword_details_clarifications' => [201],
        ],
      ],
    ], 'schools');
    $sut->modifyOntologywordDetailsFields($event);
    $result = $event->getParams();

    $this->assertEquals(['5'], $result['body'][0]['ontologyword_ids']);
    $this->assertEquals(
      [],
      $result['body'][0]['ontologyword_details_clarifications'],
    );

    // Test: no matching school year omits ontologyword_ids.
    $event = new IndexParamsEvent('schools', [
      'body' => [
        [
          'search_api_language' => ['fi'],
          'ontologyword_details_clarifications' => [301],
        ],
      ],
    ], 'schools');
    $sut->modifyOntologywordDetailsFields($event);
    $result = $event->getParams();

    $this->assertArrayNotHasKey('ontologyword_ids', $result['body'][0]);
    $this->assertEquals(
      [],
      $result['body'][0]['ontologyword_details_clarifications'],
    );
  }

}
