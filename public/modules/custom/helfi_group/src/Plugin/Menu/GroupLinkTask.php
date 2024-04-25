<?php

declare(strict_types=1);

namespace Drupal\helfi_group\Plugin\Menu;

use Drupal\Core\Menu\LocalTaskDefault;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\helfi_group\GroupUtility;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add group parameter using current node.
 */
final class GroupLinkTask extends LocalTaskDefault implements ContainerFactoryPluginInterface {

  /**
   * Constructs a new instance.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param array $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The route match service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly RouteMatchInterface $routeMatch,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition,
  ) : self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getRouteParameters(RouteMatchInterface $route_match): array {
    $routeParameters = parent::getRouteParameters($route_match);

    if (!empty($node = $this->routeMatch->getParameter('node'))) {
      if ($group = GroupUtility::getGroup($node)) {
        $routeParameters['group'] = $group->id();
      }
    }

    return $routeParameters;
  }

}
