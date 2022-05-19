<?php

declare(strict_types = 1);

namespace Drupal\helfi_group\Plugin\Menu;

use Drupal\Core\Menu\LocalTaskDefault;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\helfi_group\GroupUtility;

/**
 * Add group parameter using current node.
 */
class GroupLinkTask extends LocalTaskDefault {

  /**
   * {@inheritdoc}
   */
  public function getRouteParameters(RouteMatchInterface $route_match): array {
    $routeParameters = parent::getRouteParameters($route_match);

    if (!empty($node = \Drupal::routeMatch()->getParameter('node'))) {
      if ($group = GroupUtility::getGroup($node)) {
        $routeParameters['group'] = $group->id();
      }
    }

    return $routeParameters;
  }

}
