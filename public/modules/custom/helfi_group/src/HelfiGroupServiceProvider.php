<?php

declare(strict_types=1);

namespace Drupal\helfi_group;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Overrides the class for the menu active trail.
 */
class HelfiGroupServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('menu.active_trail');
    $definition->setClass('Drupal\helfi_group\Menu\GroupMenuActiveTrail');
    $definition
      ->addArgument(new Reference('group.group_route_context'));
  }

}
