<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_group\Kernel;

use Drupal\helfi_group\HelfiGroupServiceProvider;
use Drupal\helfi_group\Menu\GroupMenuActiveTrail;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests Helfi group service provider.
 */
class HelfiGroupServiceProviderTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'flexible_permissions',
    'group',
    'helfi_group',
  ];

  /**
   * Tests service definition altering.
   */
  public function testServiceDefinitionAlter() {
    $serviceProvider = new HelfiGroupServiceProvider();
    $serviceProvider->alter($this->container);
    $definition = $this->container->getDefinition('menu.active_trail');
    $this->assertEquals(GroupMenuActiveTrail::class, $definition->getClass());
  }

}
