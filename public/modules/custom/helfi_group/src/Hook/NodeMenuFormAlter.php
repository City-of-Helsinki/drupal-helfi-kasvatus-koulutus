<?php

declare(strict_types=1);

namespace Drupal\helfi_group\Hook;

use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Hook\Order\Order;
use Drupal\node\NodeInterface;

/**
 * Alters node form menu settings for group menus.
 */
class NodeMenuFormAlter {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {
  }

  /**
   * Implements hook_form_BASE_FORM_ID_alter() for node_form.
   *
   * #UHF-8926 the form alter must be run after group content menu's and
   * helfi_navigation's alters. Otherwise, the changes made in "menu"-render
   * array would be overridden.
   *
   * @param array<string, mixed> $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  #[Hook(hook: 'form_node_form_alter', order: Order::Last)]
  public function alter(array &$form, FormStateInterface $form_state): void {
    $form['actions']['submit']['#submit'][] = 'helfi_group_menuitem_set_content_translation_status';

    $formObject = $form_state->getFormObject();
    assert($formObject instanceof EntityFormInterface);
    $entity = $formObject->getEntity();

    if (!isset($form['menu']) || !array_key_exists('link', $form['menu'])) {
      return;
    }

    if ($entity instanceof NodeInterface) {
      /** @var \Drupal\node\NodeTypeInterface $type */
      $type = $entity->get('type')
        ->entity;

      $type_menus_ids = $type
        ->getThirdPartySetting('menu_ui', 'available_menus', ['main']);

      if (empty($type_menus_ids)) {
        // Hide menu selector if node type has no available menus.
        $form['menu']['#access'] = FALSE;

        return;
      }
    }
    $menu_parent = $form['menu']['link']['menu_parent']['#default_value'];

    if (!$menu_parent) {
      return;
    }

    $menu_name = explode(':', $menu_parent)[0];
    if (!str_contains($menu_name, 'group')) {
      return;
    }

    $storage = $this->entityTypeManager->getStorage('menu_link_content');
    $result = $storage->getQuery()
      ->condition('link.uri', "entity:node/{$entity->id()}")
      ->condition('menu_name', [$menu_name], 'IN')
      ->sort('id', 'ASC')
      ->accessCheck(FALSE)
      ->range(0, 1)
      ->execute();

    if (!$result) {
      return;
    }

    $current_language = $entity
      ->language()
      ->getId();

    /** @var \Drupal\menu_link_content\MenuLinkContentInterface|null $menu_link_content */
    $menu_link_content = $storage->load(reset($result));
    if (!$menu_link_content || !$menu_link_content->hasTranslation($current_language)) {
      return;
    }

    $menu_link_content = $menu_link_content->getTranslation($current_language);
    $status = $menu_link_content->get('content_translation_status')->value;
    $form['menu']['content_translation_status']['#default_value'] = $status;
    // @phpcs:ignore
    $form['menu']['content_translation_status']['#description'] = 'Tämä arvo muuttuu automaattisesti tallentaessa sisällön julkaisutilan mukaan.';
  }

}
