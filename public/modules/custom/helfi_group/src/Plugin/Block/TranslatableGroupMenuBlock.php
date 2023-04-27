<?php

namespace Drupal\helfi_group\Plugin\Block;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\MenuActiveTrailInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\group_content_menu\GroupContentMenuInterface;
use Drupal\group_content_menu\Plugin\Block\GroupMenuBlock;

/**
 * Provides a translatable group content menu block.
 *
 * @Block(
 *   id = "translatable_group_content_menu",
 *   admin_label = @Translation("Translatable Group Menu"),
 *   category = @Translation("Translatable Group Menus"),
 *   deriver = "Drupal\group_content_menu\Plugin\Derivative\GroupMenuBlock",
 *   context_definitions = {
 *     "group" = @ContextDefinition("entity:group", required = FALSE)
 *   }
 * )
 */
class TranslatableGroupMenuBlock extends GroupMenuBlock {

  public function __construct(
    array $configuration,
    $plugin_id,
    array $plugin_definition,
    MenuLinkTreeInterface $menu_tree,
    MenuActiveTrailInterface $menu_active_trail,
    EntityTypeManagerInterface $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $menu_tree, $menu_active_trail, $entity_type_manager);
  }

  public function build() {
    // return parent::build();

    $menu_name = 'group_menu_link_content-5'; // $this->getMenuName();
    // If unable to determine the menu, prevent the block from rendering.
    // if (!$menu_name = $this->getMenuName()) {
    // return [];
    // }
    if ($this->configuration['expand_all_items']) {
      $parameters = new MenuTreeParameters();
      $active_trail = $this->menuActiveTrail->getActiveTrailIds($menu_name);
      $parameters->setActiveTrail($active_trail);
    }
    else {
      $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters($menu_name);
    }

    // Adjust the menu tree parameters based on the block's configuration.
    $level = $this->configuration['level'];
    $depth = $this->configuration['depth'];
    $parameters->setMinDepth($level);
    // When the depth is configured to zero, there is no depth limit. When depth
    // is non-zero, it indicates the number of levels that must be displayed.
    // Hence this is a relative depth that we must convert to an actual
    // (absolute) depth, that may never exceed the maximum depth.
    if ($depth > 0) {
      $parameters->setMaxDepth(min($level + $depth - 1, $this->menuTree->maxDepth()));
    }

    $tree = $this->menuTree->load($menu_name, $parameters);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
      [
        'callable' => 'menu_block_current_language_tree_manipulator::filterLanguages',
        // 'args' => [$this->configuration['translation_providers']],
      ],
    ];
    $tree = $this->menuTree->transform($tree, $manipulators);
    $build = $this->menuTree->build($tree);
    $menu_instance = $this->getMenuInstance();
    if ($menu_instance instanceof GroupContentMenuInterface) {
      $build['#contextual_links']['group_menu'] = [
        'route_parameters' => [
          'group' => $this->getContext('group')->getContextData()->getValue()->id(),
          'group_content_menu' => $menu_instance->id(),
        ],
      ];

    }
    if ($menu_instance) {
      $build['#theme'] = 'menu__group_menu';
    }
    return $build;

  }



}
