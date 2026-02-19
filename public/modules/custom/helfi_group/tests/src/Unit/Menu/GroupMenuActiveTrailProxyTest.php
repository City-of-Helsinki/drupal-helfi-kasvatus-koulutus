<?php

declare(strict_types=1);

namespace Drupal\Tests\helfi_group\Unit\Menu;

use Drupal\helfi_group\ProxyClass\Menu\GroupMenuActiveTrail as GroupMenuActiveTrailProxy;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\helfi_group\Menu\GroupMenuActiveTrail;
use Drupal\Tests\UnitTestCase;
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
   * Tests all proxy methods delegate to the real service.
   */
  public function testProxyDelegatesAllMethodsAndLoadsServiceOnce(): void {
    $activeLink = $this->prophesize(MenuLinkInterface::class);
    $activeLink->getTitle()->willReturn('Delegated link');

    $realService = $this->prophesize(GroupMenuActiveTrail::class);
    $realService->getActiveLink('test-menu')->willReturn($activeLink->reveal());
    $realService->getActiveTrailIds('group_menu_link_content-1')->willReturn([
      'menu_link_content:abc',
      'menu_link_content:def',
    ]);
    $realService->has('cache_key')->willReturn(TRUE);
    $realService->get('cache_key')->willReturn('cached_value');
    $realService->set('key', 'value')->willReturn($realService->reveal());
    $realService->delete('key')->willReturn(TRUE);
    $realService->reset()->willReturn(NULL);
    $realService->clear()->willReturn(NULL);
    $realService->destruct()->willReturn(NULL);

    $container = $this->prophesize(ContainerInterface::class);
    $container->get(self::SERVICE_ID)->willReturn($realService->reveal());

    $proxy = new GroupMenuActiveTrailProxy($container->reveal(), self::SERVICE_ID);

    // Call every proxy method.
    $this->assertSame($activeLink->reveal(), $proxy->getActiveLink('test-menu'));
    $this->assertEquals('Delegated link', $proxy->getActiveLink('test-menu')->getTitle());
    $this->assertSame([
      'menu_link_content:abc',
      'menu_link_content:def',
    ], $proxy->getActiveTrailIds('group_menu_link_content-1'));
    $this->assertTrue($proxy->has('cache_key'));
    $this->assertSame('cached_value', $proxy->get('cache_key'));
    $proxy->set('key', 'value');
    $proxy->delete('key');
    $proxy->reset();
    $proxy->clear();
    $proxy->destruct();

    // Every proxy method must have called the corresponding real service
    // method.
    $realService->getActiveLink('test-menu')->shouldHaveBeenCalledTimes(2);
    $realService->getActiveTrailIds('group_menu_link_content-1')->shouldHaveBeenCalledOnce();
    $realService->has('cache_key')->shouldHaveBeenCalledOnce();
    $realService->get('cache_key')->shouldHaveBeenCalledOnce();
    $realService->set('key', 'value')->shouldHaveBeenCalledOnce();
    $realService->delete('key')->shouldHaveBeenCalledOnce();
    $realService->reset()->shouldHaveBeenCalledOnce();
    $realService->clear()->shouldHaveBeenCalledOnce();
    $realService->destruct()->shouldHaveBeenCalledOnce();

    // Original service must be loaded from the container only once.
    $container->get(self::SERVICE_ID)->shouldHaveBeenCalledTimes(1);
  }

}
