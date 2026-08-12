<?php

declare(strict_types=1);

namespace Drupal\helfi_kasko_content\Hook;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\group\Entity\GroupMembership;
use Drupal\helfi_kasko_content\UnitCategoryUtility;
use Drupal\helfi_platform_config\DTO\ParagraphTypeCollection;
use Drupal\user\Entity\Role;

/**
 * Entity hooks for Helfi KASKO Content.
 */
class EntityHooks {

  use AutowireTrait;
  use StringTranslationTrait;

  public function __construct(
    private readonly AccountProxyInterface $currentUser,
  ) {
  }

  /**
   * Implements hook_ENTITY_TYPE_access().
   */
  #[Hook('tpr_unit_access')]
  public static function tprUnitAccess(EntityInterface $entity, string $operation, AccountInterface $account): AccessResultInterface {
    /** @var \Drupal\helfi_tpr\Entity\Unit $entity */
    // Allow users with special permissions to update specific TPR units.
    if ($operation === 'update' && $entity->hasField('field_categories')) {
      $unit_categories = [];
      foreach ($entity->get('field_categories')->getValue() as $value) {
        if (!empty($value['value'])) {
          $unit_categories[] = $value['value'];
        }
      }

      if (in_array(UnitCategoryUtility::DAYCARE, $unit_categories)) {
        return AccessResult::allowedIfHasPermission($account, 'admin daycare units');
      }

      if (in_array(UnitCategoryUtility::COMPREHENSIVE_SCHOOL, $unit_categories)) {
        return AccessResult::allowedIfHasPermission($account, 'admin comprehensive school units');
      }

      if (in_array(UnitCategoryUtility::PLAYGROUND, $unit_categories)) {
        return AccessResult::allowedIfHasPermission($account, 'admin playground units');
      }
    }

    return AccessResult::neutral();
  }

  /**
   * Implements hook_helfi_paragraph_types().
   *
   * @return \Drupal\helfi_platform_config\DTO\ParagraphTypeCollection[]
   *   The enabled paragraph types.
   */
  #[Hook('helfi_paragraph_types')]
  public static function helfiParagraphTypes() : array {
    $entities = [
      'node' => [
        'page' => [
          'field_content' => [
            'vocational_school_search' => 18,
            'high_school_search' => 20,
            'group_news' => 21,
            'group_news_archive' => 22,
          ],
          'field_lower_content' => [
            'vocational_school_search' => 18,
            'after_school_activity_search' => 19,
            'playground_search' => 21,
            'high_school_search' => 22,
            'daycare_search' => 23,
            'group_news' => 24,
          ],
        ],
        'landing_page' => [
          'field_content' => [
            'vocational_school_search' => 18,
            'after_school_activity_search' => 19,
            'playground_search' => 20,
            'high_school_search' => 21,
            'school_search' => 22,
            'daycare_search' => 23,
            'group_news' => 24,
            'group_news_archive' => 25,
          ],
        ],
        'comprehensive_school_subpage' => [
          'field_hero' => [
            'hero' => 0,
          ],
          'field_content' => [
            'text' => 0,
            'accordion' => 1,
            'banner' => 2,
            'image' => 3,
            'list_of_links' => 4,
            'content_cards' => 5,
            'columns' => 6,
            'phasing' => 7,
            'from_library' => 8,
            'map' => 9,
            'remote_video' => 10,
            'chart' => 11,
            'event_list' => 13,
            'contact_card_listing' => 14,
            'news_list' => 15,
            'image_gallery' => 16,
            'number_highlights' => 17,
            'vocational_school_search' => 18,
            'high_school_search' => 20,
            'group_news' => 21,
            'group_news_archive' => 22,
          ],
          'field_lower_content' => [
            'list_of_links' => 0,
            'content_cards' => 1,
            'text' => 2,
            'accordion' => 3,
            'banner' => 4,
            'image' => 5,
            'columns' => 6,
            'phasing' => 7,
            'from_library' => 8,
            'map' => 9,
            'remote_video' => 10,
            'chart' => 11,
            'event_list' => 13,
            'contact_card_listing' => 14,
            'news_list' => 15,
            'image_gallery' => 16,
            'number_highlights' => 17,
            'vocational_school_search' => 18,
            'after_school_activity_search' => 19,
            'playground_search' => 21,
            'high_school_search' => 22,
            'daycare_search' => 23,
            'group_news' => 24,
          ],
        ],
      ],
    ];

    $enabled = [];
    foreach ($entities as $entityTypeId => $bundles) {
      foreach ($bundles as $bundle => $fields) {
        foreach ($fields as $field => $paragraphTypes) {
          foreach ($paragraphTypes as $paragraphType => $weight) {
            $enabled[] = new ParagraphTypeCollection($entityTypeId, $bundle, $field, $paragraphType, $weight);
          }
        }
      }
    }
    return $enabled;
  }

  /**
   * Implements hook_form_FORM_ID_alter().
   *
   * @param array<string, mixed> $form
   *   The form.
   */
  #[Hook('form_node_announcement_form_alter')]
  public function formNodeAnnouncementFormAlter(array &$form): void {
    $account = $this->currentUser->getAccount();

    $this->announcementException($form, $account);
    $this->announcementSchoolEditorException($form, $account);
  }

  /**
   * Implements hook_form_FORM_ID_alter().
   *
   * @param array<string, mixed> $form
   *   The form.
   */
  #[Hook('form_node_announcement_edit_form_alter')]
  public function formNodeAnnouncementEditFormAlter(array &$form): void {
    $account = $this->currentUser->getAccount();

    $this->announcementException($form, $account);
    $this->announcementSchoolEditorException($form, $account);
  }

  /**
   * Implements hook_field_widget_single_element_form_alter().
   *
   * @param array<string, mixed> $element
   *   The widget element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   * @param array<string, mixed> $context
   *   The widget context.
   */
  #[Hook('field_widget_single_element_form_alter')]
  public function fieldWidgetSingleElementFormAlter(array &$element, FormStateInterface $form_state, array $context): void {
    $definition = $context['items']->getFieldDefinition();

    // Linkit widget overwrites the description for school parent field.
    if ($definition->getName() === 'field_school_parent' && isset($element['uri'])) {
      $element['uri']['#description'] = $definition->getDescription();
    }
  }

  /**
   * Implements hook_entity_bundle_field_info_alter().
   *
   * @param array<string, mixed> $fields
   *   The bundle fields.
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type.
   * @param string $bundle
   *   The bundle.
   */
  #[Hook('entity_bundle_field_info_alter')]
  public function entityBundleFieldInfoAlter(array &$fields, EntityTypeInterface $entity_type, string $bundle): void {
    if (
      $entity_type->id() === 'node' &&
      $bundle === 'comprehensive_school_subpage' &&
      isset($fields['field_school_parent'])
    ) {
      $fields['field_school_parent']->addConstraint('SchoolParent');
    }
  }

  /**
   * Implements hook_helfi_toc_forms_alter().
   *
   * @param array<int, string> $forms
   *   The whitelisted form ids.
   */
  #[Hook('helfi_toc_forms_alter')]
  public function helfiTocFormsAlter(array &$forms): void {
    $forms[] = 'node_comprehensive_school_subpage_form';
    $forms[] = 'node_comprehensive_school_subpage_edit_form';
  }

  /**
   * Implements hook_hero_visibility_alter().
   *
   * @param array<int, string> $form_ids
   *   The hero form ids.
   */
  #[Hook('hero_visibility_alter')]
  public function heroVisibilityAlter(array &$form_ids): void {
    $form_ids[] = 'node_comprehensive_school_subpage_form';
    $form_ids[] = 'node_comprehensive_school_subpage_edit_form';
  }

  /**
   * UHF-10763 Alter announcement form for comprehensive school editor.
   *
   * Comprehensive school editor must be able to create announcements
   * for unit pages. Prevent site wide announcements and allow only adding
   * unit pages.
   *
   * @param array<string, mixed> $form
   *   The form.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   */
  private function announcementException(array &$form, AccountInterface $account): void {
    $user_roles = $account->getRoles(TRUE);
    if (!in_array('comprehensive_school_editor', $user_roles)) {
      return;
    }

    $roles_with_permission = array_filter(
      Role::loadMultiple(),
      fn($role) => $role->hasPermission('create announcement content')
    );
    $other_user_roles = array_intersect(array_keys($roles_with_permission), $user_roles);

    // User might have other roles than comprehensive school editor.
    // in that case, we don't want to alter anything.
    if (
      $account->hasRole('comprehensive_school_editor') &&
      count($other_user_roles) >= 2 &&
      !$account->hasRole('school_editor')
    ) {
      return;
    }

    // Set unit pages selection required (Toimipistesivu).
    $form['field_announcement_unit_pages']['widget']['#required'] = TRUE;
    $info = $this->t('Add the school you want to create the announcement for. This field is required.');
    $form['field_announcement_unit_pages']['widget']['#description'] = $info;

    // Prevent creating a site wide announcement.
    $form['field_announcement_all_pages']['widget']['value']['#default_value'] = FALSE;
    $form['field_announcement_all_pages']['#access'] = FALSE;

    // Prevent adding service pages (Palvelusivu) or content page (Sisältösivu).
    $form['field_announcement_service_pages']['#access'] = FALSE;
    $form['field_announcement_content_pages']['#access'] = FALSE;
  }

  /**
   * UHF-10889 Allow school editors to create announcements for their group.
   *
   * Create announcement which is automatically targeted to all group pages.
   * Allow announcements for unit pages. Prevented site wide announcements.
   *
   * @param array<string, mixed> $form
   *   The form.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user session.
   */
  private function announcementSchoolEditorException(array &$form, AccountInterface $account): void {
    $user_roles = $account->getRoles(TRUE);
    if (!in_array('school_editor', $user_roles)) {
      return;
    }

    $roles_with_permission = array_filter(
      Role::loadMultiple(),
      fn($role) => $role->hasPermission('create announcement content')
    );
    $other_user_roles = array_intersect(array_keys($roles_with_permission), $user_roles);

    // User might have other roles than school editor.
    // in that case, we don't want to alter anything.
    if (
      $account->hasRole('school_editor') &&
      count($other_user_roles) >= 2 &&
      !in_array('comprehensive_school_editor', $other_user_roles)
    ) {
      return;
    }

    // Always disable the fields that user may not edit.
    $form['field_announcement_all_pages']['widget']['value']['#default_value'] = FALSE;
    $form['field_announcement_all_pages']['#access'] = FALSE;

    // Prevent adding service pages (Palvelusivu)
    $form['field_announcement_service_pages']['#access'] = FALSE;

    $form['field_announcement_content_pages']['#disabled'] = TRUE;

    $form['field_announcement_unit_pages']['#disabled'] = TRUE;

    if (!$groupMemberships = GroupMembership::loadByUser($account)) {
      return;
    }

    // Get all group related nodes and set them as target pages.
    /** @var \Drupal\group\Entity\GroupMembership $membership */
    $membership = reset($groupMemberships);
    $entities = $membership->getGroup()->getRelatedEntities();

    // Preset content pages.
    $nodes = array_filter(
      $entities,
      fn($entity) =>
        $entity->getEntityTypeId() === 'node' &&
        $entity->bundle() !== 'news_item' &&
        $entity->bundle() !== 'announcement'
    );
    $node_ids = array_map(fn($entity) => $entity->id(), $nodes);

    $info = $this->t('The announcement will be shown on all pages related to your school.');
    $form['field_announcement_content_pages']['widget']['#default_value'] = $node_ids;
    $form['field_announcement_content_pages']['widget']['#description'] = $info;

    // Preset units.
    $units = array_filter(
      $entities,
      fn($entity) => $entity->getEntityTypeId() === 'tpr_unit'
    );
    $unit_ids = array_map(fn($entity) => $entity->id(), $units);

    $form['field_announcement_unit_pages']['widget']['#default_value'] = $unit_ids;
  }

}
