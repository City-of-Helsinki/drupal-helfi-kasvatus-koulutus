<?php

declare(strict_types = 1);

namespace Drupal\helfi_group;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group_content_menu\NodeFormAlter;
use Drupal\menu_link_content\Entity\MenuLinkContent;

/**
 * Helper class to handle altering unit forms.
 */
class UnitFormAlter extends NodeFormAlter {

  /**
   * Alter unit forms to use GroupContentMenu options with units in groups.
   *
   * @param array $form
   *   A form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   A form state object.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function alter(array &$form, FormStateInterface $form_state) {
    $unit = $form_state->getFormObject()->getEntity();
    $groups = $this->getGroupsForEntity($form_state, $unit);

    if (empty($groups) || !isset($form['menu'])) {
      return;
    }

    $group_menus = $this->getGroupMenus($groups);
    $defaults = $this->getMenuLinkDefault($unit, array_keys($group_menus));
    $default = $defaults['menu_name'] . ':' . $defaults['parent'];

    // Replace the menu_parent options with group menu.
    $form['menu']['link']['menu_parent'] = $this->menuParentSelector
      ->parentSelectElement($default, $defaults['id'], $group_menus);

    // Set menu access.
    $form['menu']['#access'] = FALSE;
    if (!empty($form['menu']['link']['menu_parent']['#options'])) {
      $context_id = '@group.group_route_context:group';
      $contexts = $this->contextRepository->getRuntimeContexts([$context_id]);
      $group = $contexts[$context_id]->getContextValue();
      if ($group && $group->hasPermission('manage group_content_menu', $this->currentUser)) {
        $form['menu']['#access'] = TRUE;
      }
    }
  }

  /**
   * Get an entity's groups.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   A form state object.
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   An entity.
   *
   * @return array
   *   An empty array or an array of groups.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getGroupsForEntity(FormStateInterface $form_state, ContentEntityInterface $entity): array {
    // Get group for new entity.
    if ($group = $form_state->get('group')) {
      return [$group];
    }

    // Get groups for exising entity.
    $group_contents = $this->entityTypeManager
      ->getStorage('group_content')
      ->loadByEntity($entity);

    $group_ids = [];
    foreach ($group_contents as $group_content) {
      /** @var \Drupal\group\Entity\GroupContent $group_content */
      $group_ids[] = $group_content->getGroup()->id();
    }

    return $this->entityTypeManager
      ->getStorage('group')
      ->loadMultiple($group_ids);
  }

  /**
   * Returns the definition for a menu link for the given entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   * @param array $menu_names
   *   The menu names.
   *
   * @return array
   *   An array that contains default values for the menu link form.
   */
  protected function getMenuLinkDefault(ContentEntityInterface $entity, array $menu_names): array {
    $menu_name = 'main';
    $defaults = [
      'entity_id' => 0,
      'id' => '',
      'title' => '',
      'title_max_length' => 128,
      'description' => '',
      'description_max_length' => 128,
      'menu_name' => $menu_name,
      'parent' => '',
      'weight' => 0,
    ];
    if (empty($menu_names)) {
      return $defaults;
    }

    if ($entity->id()) {
      $query = \Drupal::entityQuery('menu_link_content')
        ->condition('link.uri', sprintf('entity:%s/%s', $entity->getEntityTypeId(), $entity->id()))
        ->condition('menu_name', $menu_names, 'IN')
        ->sort('id', 'ASC')
        ->range(0, 1);
      $result = $query->execute();

      $id = !empty($result) ? reset($result) : FALSE;
      if ($id) {
        /** @var \Drupal\menu_link_content\Entity\MenuLinkContent $menu_link */
        $menu_link = MenuLinkContent::load($id);
        $defaults = [
          'entity_id' => $menu_link->id(),
          'id' => $menu_link->getPluginId(),
          'title' => $menu_link->getTitle(),
          'title_max_length' => $menu_link->getFieldDefinitions()['title']->getSetting('max_length'),
          'description' => $menu_link->getDescription(),
          'description_max_length' => $menu_link->getFieldDefinitions()['description']->getSetting('max_length'),
          'menu_name' => $menu_link->getMenuName(),
          'parent' => $menu_link->getParentId(),
          'weight' => $menu_link->getWeight(),
        ];
      }
    }

    return $defaults;
  }

}
