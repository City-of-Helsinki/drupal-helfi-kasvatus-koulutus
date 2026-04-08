<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_kasko_content\Kernel\EventSubscriber;

use Drupal\helfi_kasko_content\EventSubscriber\UnitCategorySubscriber;
use Drupal\helfi_kasko_content\UnitCategoryUtility;
use Drupal\KernelTests\KernelTestBase;
use Drupal\migrate\Event\MigratePreRowSaveEvent;
use Drupal\migrate\MigrateMessageInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the UnitCategorySubscriber.
 */
#[Group('helfi_kasko_content')]
#[RunTestsInSeparateProcesses]
#[CoversClass(UnitCategorySubscriber::class)]
class UnitCategorySubscriberTest extends KernelTestBase {

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
  private UnitCategorySubscriber $sut;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->sut = new UnitCategorySubscriber();
  }

  /**
   * Test no results.
   */
  public function testMigrationIsIgnored(): void {
    $row = new Row(['ontologyword_ids' => [603]]);
    $event = $this->createEvent('some_other_migration', $row);
    $this->sut->preRowSave($event);

    // Tests that non-tpr_unit migrations are ignored.
    $this->assertEmpty($row->getDestination());

    $row = new Row(['ontologyword_ids' => []]);
    $event = $this->createEvent('tpr_unit', $row);
    $this->sut->preRowSave($event);

    // Tests that empty ontologyword_ids returns early.
    $this->assertEmpty($row->getDestination());

    $row = new Row(['ontologyword_ids' => [99999]]);
    $event = $this->createEvent('tpr_unit', $row);
    $this->sut->preRowSave($event);

    $destination = $row->getDestination();

    // Tests unknown ontologyword_id produces no categories.
    $this->assertEquals([], $destination['field_categories']);
  }

  /**
   * Tests multiple ontologyword_ids produce deduplicated categories.
   */
  public function testMultipleOntologywordIdsDeduplicateCategories(): void {
    // 601 maps to: comprehensive school, Swedish basic education, grades 1-6.
    // 661 maps to: comprehensive school, Finnish basic education, grades 1-6.
    // "comprehensive school" and "grades 1-6" appear in both, should be
    // deduplicated.
    $row = new Row(['ontologyword_ids' => [601, 661]]);
    $event = $this->createEvent('tpr_unit', $row);
    $this->sut->preRowSave($event);

    $destination = $row->getDestination();
    $categories = $destination['field_categories'];
    $expected = [
      UnitCategoryUtility::COMPREHENSIVE_SCHOOL,
      UnitCategoryUtility::SWEDISH_BASIC_EDUCATION,
      UnitCategoryUtility::GRADES_1_6,
      UnitCategoryUtility::FINNISH_BASIC_EDUCATION,
    ];
    sort($categories);
    sort($expected);
    $this->assertEquals($expected, $categories);
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
