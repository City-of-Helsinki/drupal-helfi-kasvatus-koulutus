<?php

declare(strict_types=1);

namespace Drupal\helfi_group\Plugin\Block;

use Drupal\group_content_menu\GroupContentMenuInterface;
use Drupal\group_content_menu\Plugin\Block\GroupMenuBlock as OriginalGroupMenuBlock;
use Drupal\node\NodeInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Overrides the Group Content Menu block.
 */
final class GroupMenuBlock extends OriginalGroupMenuBlock implements ContainerFactoryPluginInterface {

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The menu link manager service.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $menuLinkManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->routeMatch = $container->get('current_route_match');
    $instance->menuLinkManager = $container->get('plugin.manager.menu.link');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $menu_name = $this->getMenuName();
    // If unable to determine the menu, prevent the block from rendering.
    if (!$menu_name) {
      return [];
    }

    // Get block configuration and menu tree parameters.
    $level = $this->configuration['level'];
    $depth = $this->configuration['depth'];
    $relative_visibility = $this->configuration['relative_visibility'] ?? FALSE;
    $parameters = $this->menuTree->getCurrentRouteMenuTreeParameters($menu_name);

    // If active trail is empty, we might be on a group news item, that
    // is not included in the menu tree. This fallback handling for empty
    // active trail is the only abstraction from the parent class build method.
    if (empty(array_filter($parameters->activeTrail))) {
      $parameters->setActiveTrail($this->getActiveTrailForNewsItem($menu_name));
    }

    // Adjust the menu tree parameters based on the block's configuration.
    $parameters->setMinDepth($level);

    // Adjust menu root in cases where the active menu item is below root and
    // only a subset of the full menu is to be shown, or possibly not at all.
    $relative_level = count($parameters->activeTrail);
    if ($level > 1 || ($relative_visibility && $relative_level > $level)) {
      $menu_trail_ids = array_reverse(array_values($parameters->activeTrail));
      // For relative visibility we reset the root relative to the active
      // menu item.
      if ($relative_visibility) {
        $menu_root = $menu_trail_ids[$relative_level - 2];
        $parameters->setRoot($menu_root);
      }
      // For absolute visibility, we reset root relative to the original and
      // adjust the minimum depth.
      elseif ($relative_level >= $level) {
        $menu_root = $menu_trail_ids[$level - 1];
        $parameters->setRoot($menu_root)->setMinDepth(1);
      }
      // If the active menu item is not at or above the visibility level, and
      // relative visibility is not in play, then do not show the menu.
      else {
        return [];
      }
    }

    // When the depth is configured to zero, there is no depth limit. When depth
    // is non-zero, it indicates the number of levels that must be displayed.
    // Hence, this is a relative depth that we must convert to an actual
    // (absolute) depth, that may never exceed the maximum depth.
    if ($depth > 0) {
      $relative_depth = $relative_visibility ? $level + $depth - 1 : $depth;
      $parameters->setMaxDepth(\min($relative_depth, $this->menuTree->maxDepth()));
    }

    // If expandedParents is empty, the whole menu tree is built.
    if ($this->configuration['expand_all_items']) {
      $parameters->expandedParents = [];
    }

    $tree = $this->menuTree->load($menu_name, $parameters);
    $tree = $this->menuTree->transform($tree, $this->getMenuManipulators());
    $build = $this->menuTree->build($tree);
    $menu_instance = $this->getMenuInstance();
    $build['#group_content_menu'] = [
      'menu_name' => $menu_name,
      'theme_hook_suggestion' => $this->configuration['theme_hook_suggestion'],
    ];
    if ($menu_instance instanceof GroupContentMenuInterface) {
      $build['#group_content_menu']['group_content_menu_type'] = $menu_instance->bundle();
      $build['#contextual_links']['group_menu'] = [
        'route_parameters' => [
          'group' => $this->getContext('group')->getContextData()->getValue()->id(),
          'group_content_menu' => $menu_instance->id(),
        ],
      ];

    }
    $build['#theme'] = 'menu';
    return $build;
  }

  /**
   * Get the active trail for a news item node.
   *
   * @param string $menu_name
   *   The menu name.
   *
   * @return array
   *   The active trail.
   */
  private function getActiveTrailForNewsItem(string $menu_name): array {
    $active_trail = ['' => ''];

    // Fetch current node and only continue if it's a news_item.
    $node = $this->routeMatch->getParameter('node');
    if (!$node || !($node instanceof NodeInterface) || $node->bundle() !== 'news_item') {
      return $active_trail;
    }

    // Fetch current group from plugin context.
    $group = $this->getContextValue('group');
    if (!$group || !($group instanceof GroupInterface)) {
      return $active_trail;
    }

    // Fetch news parent from group.
    $newsParent = NULL;
    if ($group->hasField('field_group_news_parent')) {
      $newsParent = $group->get('field_group_news_parent')->entity;
    }
    if (!$newsParent || !($newsParent instanceof NodeInterface)) {
      return $active_trail;
    }

    // Build active trail from news parent.
    $found = NULL;
    $links = [];
    $route_name = $newsParent->toUrl()->getRouteName();
    if ($route_name) {
      $route_parameters = $newsParent->toUrl()->getRouteParameters();

      // Load links matching this route.
      $links = $this->menuLinkManager->loadLinksByRoute($route_name, $route_parameters, $menu_name);
    }
    if ($links) {
      $found = reset($links);
    }
    if ($found && $parents = $this->menuLinkManager->getParentIds($found->getPluginId())) {
      $active_trail = $parents + $active_trail;
    }

    return $active_trail;
  }

}
