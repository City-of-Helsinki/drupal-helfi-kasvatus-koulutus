<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_kasko_content\Kernel\EventSubscriber;

use Drupal\helfi_kasko_content\EventSubscriber\UnitOntologyWordDetailsSubscriber;
use Drupal\helfi_tpr\Entity\OntologyWordDetails;
use Drupal\KernelTests\KernelTestBase;
use Drupal\migrate\Event\MigratePreRowSaveEvent;
use Drupal\migrate\MigrateMessageInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the UnitOntologyWordDetailsSubscriber.
 */
#[Group('helfi_kasko_content')]
#[RunTestsInSeparateProcesses]
#[CoversClass(UnitOntologyWordDetailsSubscriber::class)]
class UnitOntologyWordDetailsSubscriberTest extends KernelTestBase {

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
   * The subscriber under test.
   */
  private UnitOntologyWordDetailsSubscriber $sut;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('tpr_ontology_word_details');
    $this->installEntitySchema('user');

    $this->sut = $this->container->get(UnitOntologyWordDetailsSubscriber::class);
  }

  /**
   * Test no results.
   */
  public function testEarlyReturn(): void {
    $row = new Row(['ontologyword_ids' => [100]]);
    $row->setDestinationProperty('id', 42);

    $event = $this->createEvent('some_other_migration', $row);
    $this->sut->preRowSave($event);

    // Tests that non-tpr_unit migrations are ignored.
    $this->assertArrayNotHasKey('field_ontologyword_details', $row->getDestination());

    $row = new Row(['ontologyword_ids' => []]);
    $row->setDestinationProperty('id', 42);

    $event = $this->createEvent('tpr_unit', $row);
    $this->sut->preRowSave($event);

    // Tests that empty ontologyword_ids returns early.
    $this->assertArrayNotHasKey('field_ontologyword_details', $row->getDestination());

    $row = new Row(['ontologyword_ids' => [999]]);
    $row->setDestinationProperty('id', 42);

    $event = $this->createEvent('tpr_unit', $row);
    $this->sut->preRowSave($event);

    $destination = $row->getDestination();
    $this->assertEquals([], $destination['field_ontologyword_details']);
  }

  /**
   * Tests that existing ontologyword details entities are referenced.
   *
   * Non-existing ontologyword details should be excluded.
   */
  public function testExistingOntologywordDetailsAreReferenced(): void {
    OntologyWordDetails::create([
      'id' => '100_42',
      'name' => 'Detail 100_42',
      'ontologyword_id' => '100',
    ])->save();
    OntologyWordDetails::create([
      'id' => '200_42',
      'name' => 'Detail 200_42',
      'ontologyword_id' => '200',
    ])->save();

    $row = new Row(['ontologyword_ids' => [100, 200, 999]]);
    $row->setDestinationProperty('id', 42);

    $event = $this->createEvent('tpr_unit', $row);
    $this->sut->preRowSave($event);

    $destination = $row->getDestination();
    $this->assertEquals(['100_42', '200_42'], $destination['field_ontologyword_details']);
  }

  /**
   * Creates a MigratePreRowSaveEvent with the given migration ID and row.
   */
  private function createEvent(string $migrationId, Row $row): MigratePreRowSaveEvent {
    $migration = $this->createMock(MigrationInterface::class);
    $migration->method('id')->willReturn($migrationId);
    $message = $this->createMock(MigrateMessageInterface::class);

    return new MigratePreRowSaveEvent($migration, $message, $row);
  }

}
