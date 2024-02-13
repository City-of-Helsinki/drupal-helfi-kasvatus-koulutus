<?php

declare(strict_types=1);

namespace Drupal\helfi_group\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens route events to set the _admin_route for Group module views.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    $adminRoutes = [
      'view.group_members.page_1',
      'view.group_nodes.page_1',
      'view.group_tpr_units.page_1',
    ];
    foreach ($collection->all() as $name => $route) {
      if (in_array($name, $adminRoutes)) {
        $route->setOption('_admin_route', TRUE);
      }
    }
  }

}
