<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_group\Unit\Menu;

use Drupal\helfi_group\ProxyClass\Menu\GroupMenuActiveTrail as GroupMenuActiveTrailProxy;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\helfi_group\Menu\GroupMenuActiveTrail;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Unit tests for the GroupMenuActiveTrail proxy class.
 */
class GroupMenuActiveTrailProxyTest extends UnitTestCase {

  /**
   * The proxied service ID.
   */
  private const SERVICE_ID = 'helfi_group.group_menu_active_trail';

  /**
   * Tests that the proxy delegates getActiveLink() to the real service.
   */
  public function testGetActiveLinkDelegatesToRealService(): void {
    $activeLink = $this->prophesize(MenuLinkInterface::class);
    $activeLink->getTitle()->willReturn('Delegated link');

    $realService = $this->prophesize(GroupMenuActiveTrail::class);
    $realService->getActiveLink('test-menu')->willReturn($activeLink->reveal());

    $container = $this->prophesize(ContainerInterface::class);
    $container->get(self::SERVICE_ID)->willReturn($realService->reveal());

    $proxy = new GroupMenuActiveTrailProxy($container->reveal(), self::SERVICE_ID);

    $result = $proxy->getActiveLink('test-menu');

    $this->assertSame($activeLink->reveal(), $result);
    $this->assertEquals('Delegated link', $result->getTitle());
    $realService->getActiveLink('test-menu')->shouldHaveBeenCalledOnce();
    $container->get(self::SERVICE_ID)->shouldHaveBeenCalledOnce();
  }

  /**
   * Tests that the proxy delegates getActiveTrailIds() to the real service.
   */
  public function testGetActiveTrailIdsDelegatesToRealService(): void {
    $trailIds = ['menu_link_content:abc', 'menu_link_content:def'];

    $realService = $this->prophesize(GroupMenuActiveTrail::class);
    $realService->getActiveTrailIds('group_menu_link_content-1')->willReturn($trailIds);

    $container = $this->prophesize(ContainerInterface::class);
    $container->get(self::SERVICE_ID)->willReturn($realService->reveal());

    $proxy = new GroupMenuActiveTrailProxy($container->reveal(), self::SERVICE_ID);

    $result = $proxy->getActiveTrailIds('group_menu_link_content-1');

    $this->assertSame($trailIds, $result);
    $realService->getActiveTrailIds('group_menu_link_content-1')->shouldHaveBeenCalledOnce();
  }

  /**
   * Tests that the real service is only loaded once.
   */
  public function testServiceIsLazyLoadedOnce(): void {
    $realService = $this->prophesize(GroupMenuActiveTrail::class);
    $realService->getActiveLink(Argument::any())->willReturn(NULL);
    $realService->getActiveTrailIds(Argument::any())->willReturn([]);

    $container = $this->prophesize(ContainerInterface::class);
    $container->get(self::SERVICE_ID)->willReturn($realService->reveal());

    $proxy = new GroupMenuActiveTrailProxy($container->reveal(), self::SERVICE_ID);

    $proxy->getActiveLink('menu');
    $proxy->getActiveTrailIds('menu');

    $container->get(self::SERVICE_ID)->shouldHaveBeenCalledTimes(1);
  }

}
