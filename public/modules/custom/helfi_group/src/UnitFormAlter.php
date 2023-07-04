<?php

declare(strict_types = 1);

namespace Drupal\helfi_group;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group_content_menu\NodeFormAlter;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\menu_link_content\MenuLinkContentInterface;

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
  public function alter(array &$form, FormStateInterface $form_state): void {
    $unit = $form_state->getFormObject()->getEntity();
    $groups = $this->getEntityGroups($form_state, $unit);

    if (empty($groups) || !isset($form['menu'])) {
      return;
    }

    $groupMenus = $this->getGroupMenus($groups);
    $menuLink = $this->getDefaultMenuLink($unit, array_keys($groupMenus));
    $form_state->set('menu_link', $menuLink);

    // Alter the relevant menu parts.
    $form['menu']['enabled'] = [
      '#type' => 'checkbox',
      '#title' => t('Provide a menu link'),
      '#default_value' => !$menuLink->isNew() && $menuLink->hasTranslation($unit->language()->getId()),
    ];

    $form['menu']['published'] = [
      '#type' => 'checkbox',
      '#title' => t('Enabled'),
      '#default_value' => $menuLink->isNew() || $menuLink->isPublished(),
      '#states' => [
        'invisible' => [
          'input[name="menu[enabled]"]' => ['checked' => FALSE],
        ],
      ],
      '#description' => t('All languages'),
    ];

    $form['menu']['link']['title'] = [
      '#type' => 'textfield',
      '#title' => t('Menu link title'),
      '#default_value' => $menuLink->label(),
    ];

    $id = $menuLink->isNew() ? '' : $menuLink->getPluginId();
    $default = $menuLink->getMenuName() . ':' . $menuLink->getParentId();

    // Replace the menu_parent options with group menu.
    $form['menu']['link']['menu_parent'] = $this
      ->menuParentSelector
      ->parentSelectElement($default, $id, $groupMenus);

    // Set group menu access.
    $form['menu']['#access'] = FALSE;
    if (!empty($form['menu']['link']['menu_parent']['#options'])) {
      $contextId = '@group.group_route_context:group';
      $contexts = $this->contextRepository->getRuntimeContexts([$contextId]);
      $group = $contexts[$contextId]->getContextValue();
      if ($group && $group->hasPermission('manage group_content_menu', $this->currentUser)) {
        $form['menu']['#access'] = TRUE;
      }
    }

  }

  /**
   * Get entity's groups.
   *
   * @param \Drupal\Core\Form\FormStateInterface $formState
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
  protected function getEntityGroups(FormStateInterface $formState, ContentEntityInterface $entity): array {
    // Get the group for new entity.
    if ($group = $formState->get('group')) {
      return [$group];
    }

    // Get the groups for existing entity.
    $groupContents = $this->entityTypeManager
      ->getStorage('group_content')
      ->loadByEntity($entity);

    $group_ids = [];
    foreach ($groupContents as $groupContent) {
      /** @var \Drupal\group\Entity\GroupRelationship $groupContent */
      $group_ids[] = $groupContent->getGroup()->id();
    }

    return $this->entityTypeManager
      ->getStorage('group')
      ->loadMultiple($group_ids);
  }

  /**
   * Gets specific menu's default menu link for given translation.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   * @param array $menuNames
   *   The menu names.
   *
   * @return \Drupal\menu_link_content\MenuLinkContentInterface
   *   The menu link.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getDefaultMenuLink(ContentEntityInterface $entity, array $menuNames) : MenuLinkContentInterface {
    /** @var \Drupal\menu_link_content\Entity\MenuLinkContent $menuLink */
    if (!$menuLink = $entity->get('menu_link')->entity) {
      $storage = $this->entityTypeManager
        ->getStorage('menu_link_content');

      $results = $storage->getQuery()
        ->condition('link.uri', sprintf('entity:%s/%s', $entity->getEntityTypeId(), $entity->id()))
        ->condition('menu_name', $menuNames, 'IN')
        ->sort('id')
        ->range(0, 1)
        ->accessCheck(FALSE)
        ->execute();

      $menuLink = empty($results) ? MenuLinkContent::create([]) : MenuLinkContent::load(reset($results));
    }
    return \Drupal::service('entity.repository')->getTranslationFromContext($menuLink);
  }

}
