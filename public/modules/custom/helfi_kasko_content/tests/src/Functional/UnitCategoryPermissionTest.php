<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi_kasko_content\Functional;

use Drupal\Core\Url;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\helfi_tpr\Functional\MigrationTestBase;
use Drupal\Tests\helfi_tpr\Traits\TprMigrateTrait;

/**
 * Tests unit category permissions.
 *
 * @group helfi_kasko_content
 */
class UnitCategoryPermissionTest extends MigrationTestBase {

  use TprMigrateTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_kasko_content',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
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

    \Drupal::service('entity_display.repository')
      ->getFormDisplay($fieldCategoriesConfig->getTargetEntityTypeId(), $fieldCategoriesConfig->getTargetBundle())
      ->setComponent($fieldCategoriesConfig->getName(), [
        'type' => 'readonly_field_widget',
      ])
      ->save();

    $this->runUnitMigrate();
  }

  /**
   * Tests unit category permissions for unit updates.
   */
  public function testCategoryPermissions() : void {
    $entityId = 1;

    // Test that privileged account always has access.
    $this->drupalLogin($this->privilegedAccount);
    $this->drupalGet(Url::fromRoute('entity.tpr_unit.edit_form', ['tpr_unit' => $entityId]));
    $this->assertSession()->statusCodeEquals(200);

    // Test that anonymous user has no access.
    $this->drupalLogout();
    $this->drupalGet(Url::fromRoute('entity.tpr_unit.edit_form', ['tpr_unit' => $entityId]));
    $this->assertSession()->statusCodeEquals(403);

    // Test that logged-in user without special permissions has no access.
    $this->drupalLogin($this->createUser());
    $this->drupalGet(Url::fromRoute('entity.tpr_unit.edit_form', ['tpr_unit' => $entityId]));
    $this->assertSession()->statusCodeEquals(403);

    // Test that user with special category-related permission has access.
    // @todo Fix this: changes made in UnitCategorySubscriber are not shown.
    $this->drupalLogin($this->createUser([
      'admin daycare units',
    ]));
    $this->drupalGet(Url::fromRoute('entity.tpr_unit.edit_form', ['tpr_unit' => $entityId]));
    $this->assertSession()->statusCodeEquals(200);

    // Test that user with wrong category-related permission has no access.
    $this->drupalLogin($this->createUser([
      'admin playground units',
    ]));
    $this->drupalGet(Url::fromRoute('entity.tpr_unit.edit_form', ['tpr_unit' => $entityId]));
    $this->assertSession()->statusCodeEquals(403);
  }

}
