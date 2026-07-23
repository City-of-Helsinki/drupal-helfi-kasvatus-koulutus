<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_kasko_content\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\helfi_kasko_content\Hook\EntityHooks;
use Drupal\helfi_kasko_content\UnitCategoryUtility;
use Drupal\helfi_platform_config\DTO\ParagraphTypeCollection;
use Drupal\helfi_tpr\Entity\Unit;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\Entity\Role;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests entity hooks.
 */
#[Group('helfi_kasko_content')]
#[RunTestsInSeparateProcesses]
class EntityHooksTest extends KernelTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'helfi_kasko_content',
    'helfi_tpr',
    'helfi_api_base',
    'system',
    'user',
    'field',
    'link',
    'address',
    'text',
    'media',
    'telephone',
    'image',
    'file',
    'menu_link_content',
    'group',
    'options',
    'entity',
    'flexible_permissions',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('tpr_unit');
    $this->installEntitySchema('user');
    $this->installEntitySchema('group_relationship');

    FieldStorageConfig::create([
      'field_name' => 'field_categories',
      'entity_type' => 'tpr_unit',
      'type' => 'string',
      'cardinality' => -1,
    ])->save();

    FieldConfig::create([
      'field_name' => 'field_categories',
      'entity_type' => 'tpr_unit',
      'bundle' => 'tpr_unit',
      'label' => 'Categories',
    ])->save();
  }

  /**
   * Creates a role with permission to create announcements.
   */
  private function createAnnouncementRole(string $id): void {
    $role = Role::create(['id' => $id, 'label' => $id]);
    $role->grantPermission('create announcement content');
    $role->save();
  }

  /**
   * Data provider for unit category access.
   *
   * @return array<int, array{string, string}>
   *   Each set has a category and its permission.
   */
  public static function categoryProvider(): array {
    return [
      [UnitCategoryUtility::DAYCARE, 'admin daycare units'],
      [UnitCategoryUtility::COMPREHENSIVE_SCHOOL, 'admin comprehensive school units'],
      [UnitCategoryUtility::PLAYGROUND, 'admin playground units'],
    ];
  }

  /**
   * Test that the category permission grants update access to the unit.
   */
  #[DataProvider('categoryProvider')]
  public function testTprUnitAccess(string $category, string $permission): void {
    $unit = Unit::create([
      'id' => 1,
      'name' => 'Unit',
      'field_categories' => [['value' => $category]],
    ]);
    $unit->save();

    $account = $this->createUser([$permission]);

    $access = EntityHooks::tprUnitAccess($unit, 'update', $account);
    $this->assertTrue($access->isAllowed());
  }

  /**
   * Test that the search paragraph types are enabled.
   */
  public function testHelfiParagraphTypes(): void {
    $result = EntityHooks::helfiParagraphTypes();

    $this->assertNotEmpty($result);
    $this->assertContainsOnlyInstancesOf(ParagraphTypeCollection::class, $result);
  }

  /**
   * Test that the comprehensive school editor may only target unit pages.
   */
  public function testAnnouncementFormAlterForComprehensiveSchoolEditor(): void {
    $this->createAnnouncementRole('comprehensive_school_editor');

    $user = $this->createUser();
    $user->addRole('comprehensive_school_editor');
    $user->save();
    $this->setCurrentUser($user);

    $form = [
      'field_announcement_unit_pages' => ['widget' => []],
      'field_announcement_all_pages' => ['widget' => ['value' => []]],
      'field_announcement_service_pages' => [],
      'field_announcement_content_pages' => [],
    ];
    (new EntityHooks($this->container->get('current_user')))->formNodeAnnouncementFormAlter($form);

    $this->assertTrue($form['field_announcement_unit_pages']['widget']['#required']);
    $this->assertFalse($form['field_announcement_all_pages']['#access']);
    $this->assertFalse($form['field_announcement_service_pages']['#access']);
    $this->assertFalse($form['field_announcement_content_pages']['#access']);
  }

  /**
   * Test that the edit form is altered for the comprehensive school editor.
   */
  public function testAnnouncementEditFormAlterForComprehensiveSchoolEditor(): void {
    $this->createAnnouncementRole('comprehensive_school_editor');

    $user = $this->createUser();
    $user->addRole('comprehensive_school_editor');
    $user->save();
    $this->setCurrentUser($user);

    $form = [
      'field_announcement_unit_pages' => ['widget' => []],
      'field_announcement_all_pages' => ['widget' => ['value' => []]],
      'field_announcement_service_pages' => [],
      'field_announcement_content_pages' => [],
    ];
    (new EntityHooks($this->container->get('current_user')))->formNodeAnnouncementEditFormAlter($form);

    $this->assertTrue($form['field_announcement_unit_pages']['widget']['#required']);
    $this->assertFalse($form['field_announcement_all_pages']['#access']);
  }

  /**
   * Test that the school editor may not edit the target page fields.
   */
  public function testAnnouncementFormAlterForSchoolEditor(): void {
    $this->createAnnouncementRole('school_editor');

    $user = $this->createUser();
    $user->addRole('school_editor');
    $user->save();
    $this->setCurrentUser($user);

    $form = [
      'field_announcement_all_pages' => ['widget' => ['value' => []]],
      'field_announcement_service_pages' => [],
      'field_announcement_content_pages' => [],
      'field_announcement_unit_pages' => [],
    ];
    (new EntityHooks($this->container->get('current_user')))->formNodeAnnouncementFormAlter($form);

    $this->assertFalse($form['field_announcement_all_pages']['#access']);
    $this->assertFalse($form['field_announcement_service_pages']['#access']);
    $this->assertTrue($form['field_announcement_content_pages']['#disabled']);
    $this->assertTrue($form['field_announcement_unit_pages']['#disabled']);
  }

}
