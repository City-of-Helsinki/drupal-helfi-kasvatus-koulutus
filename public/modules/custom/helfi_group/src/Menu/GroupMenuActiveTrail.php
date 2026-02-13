<?php

declare(strict_types=1);

namespace Drupal\helfi_group\Menu;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Menu\MenuActiveTrail;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\group\Context\GroupRouteContext;
use Drupal\node\NodeInterface;

/**
 * Overrides the default implementation of the active menu trail service.
 */
class GroupMenuActiveTrail extends MenuActiveTrail {

  /**
   * The group context.
   *
   * @var \Drupal\group\Context\GroupRouteContext
   */
  protected $groupRouteContext;

  /**
   * Constructs a \Drupal\Core\Menu\MenuActiveTrail object.
   *
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menu_link_manager
   *   The menu link plugin manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   A route match object for finding the active link.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache backend.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock backend.
   * @param \Drupal\Core\Path\PathMatcherInterface|null $pathMatcher
   *   The path.matcher service.
   * @param \Drupal\group\Context\GroupRouteContext $group_route_context
   *   The group route context.
   */
  public function __construct(
    MenuLinkManagerInterface $menu_link_manager,
    RouteMatchInterface $route_match,
    CacheBackendInterface $cache,
    LockBackendInterface $lock,
    ?PathMatcherInterface $pathMatcher = NULL,
    ?GroupRouteContext $group_route_context = NULL,
  ) {
    parent::__construct($menu_link_manager, $route_match, $cache, $lock, $pathMatcher);
    $this->groupRouteContext = $group_route_context;
  }

  /**
   * {@inheritdoc}
   *
   * Handles active trail for group news items, which are not included in
   * the menu tree. Instead, the active trail is built from the news parent
   * node.
   */
  public function getActiveLink($menu_name = NULL) {
    // If the menu name is not a group menu, use the default implementation.
    if (!str_starts_with($menu_name, 'group_menu_link_content-')) {
      return parent::getActiveLink($menu_name);
    }

    // If this is not a news item, use the default implementation.
    $node = $this->routeMatch->getParameter('node');
    if (!$node || !($node instanceof NodeInterface) || $node->bundle() !== 'news_item') {
      return parent::getActiveLink($menu_name);
    }

    // If no group is found, use the default implementation.
    $group = $this->groupRouteContext->getBestCandidate();
    if (!$group) {
      return parent::getActiveLink($menu_name);
    }

    // If no news parent is found, use the default implementation.
    $newsParent = NULL;
    if ($group->hasField('field_group_news_parent')) {
      $newsParent = $group->get('field_group_news_parent')->entity;
    }
    if (!$newsParent || !($newsParent instanceof NodeInterface)) {
      return parent::getActiveLink($menu_name);
    }

    // The menu links coming from the storage are already sorted by depth,
    // weight and ID.
    $found = NULL;
    $links = [];

    $route_name = $newsParent->toUrl()->getRouteName();
    if ($route_name) {
      $route_parameters = $newsParent->toUrl()->getRouteParameters();

      // Load links matching this route.
      $links = $this->menuLinkManager->loadLinksByRoute($route_name, $route_parameters, $menu_name);
    }

    // Select the first matching link.
    if ($links) {
      $found = reset($links);
    }
    return $found;
  }

}
