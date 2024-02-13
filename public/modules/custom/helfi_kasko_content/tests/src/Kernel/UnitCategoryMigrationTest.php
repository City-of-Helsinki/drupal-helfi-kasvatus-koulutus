<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_kasko_content\Kernel;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\helfi_kasko_content\EventSubscriber\UnitCategorySubscriber;
use Drupal\helfi_kasko_content\UnitCategoryUtility;
use Drupal\helfi_tpr\Entity\Unit;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigratePreRowSaveEvent;
use Drupal\Tests\helfi_tpr\Kernel\MigrationTestBase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Tests TPR Unit migration for categories field.
 *
 * @group helfi_kasko_content
 */
class UnitCategoryMigrationTest extends MigrationTestBase implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public function setUp() : void {
    parent::setUp();

    FieldStorageConfig::create([
      'field_name' => 'field_categories',
      'entity_type' => 'tpr_unit',
      'type' => 'string',
      'settings' => [],
      'cardinality' => -1,
    ])->save();

    $fieldCategoriesConfig = FieldConfig::create([
      'field_name' => 'field_categories',
      'label' => 'Categories',
      'entity_type' => 'tpr_unit',
      'bundle' => 'tpr_unit',
      'required' => FALSE,
      'settings' => [],
      'description' => '',
    ]);
    $fieldCategoriesConfig->save();

    $this->runUnitMigrate();
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() : array {
    return [
      MigrateEvents::PRE_ROW_SAVE => 'preRowSave',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container): void {
    parent::register($container);
    $container
      ->register('helfi_kasko_content.migrate_subscriber', self::class)
      ->addTag('event_subscriber');
    $container->set('helfi_kasko_content.migrate_subscriber', $this);
  }

  /**
   * Use UnitCategorySubscriber to handle migration event.
   */
  public function preRowSave(MigratePreRowSaveEvent $event) : void {
    (new UnitCategorySubscriber())->preRowSave($event);
  }

  /**
   * Tests unit category mapping to ontologyword IDs.
   */
  public function testUnitCategories() : void {
    $expectedResults = [
      60321 => [],
      67763 => [],
      63115 => [],
      1 => [
        UnitCategoryUtility::COMPREHENSIVE_SCHOOL,
        UnitCategoryUtility::DAYCARE,
      ],
      59369 => [],
    ];

    foreach ($expectedResults as $entityId => $expectedCategories) {
      $unitCategories = [];
      foreach (Unit::load($entityId)->get('field_categories')->getValue() as $value) {
        if (!empty($value['value'])) {
          $unitCategories[] = $value['value'];
        }
      }

      foreach ($expectedCategories as $expectedCategory) {
        $this->assertContains($expectedCategory, $unitCategories);
      }
    }
  }

}
