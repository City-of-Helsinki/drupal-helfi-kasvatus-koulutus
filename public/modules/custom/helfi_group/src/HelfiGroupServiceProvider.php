<?php

declare(strict_types=1);

namespace Drupal\helfi_group;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\helfi_group\Menu\GroupMenuActiveTrail;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Overrides the class for the menu active trail.
 */
class HelfiGroupServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    if ($container->hasDefinition('menu.active_trail')) {
      $definition = $container->getDefinition('menu.active_trail');
      $definition->setClass(GroupMenuActiveTrail::class);
      $definition
        ->addArgument(new Reference('group.group_route_context'));
    }
  }

}
